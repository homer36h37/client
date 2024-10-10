<?php

namespace Client;

use Client\Commands\Cache;
use Client\Commands\Composer;
use Client\Commands\Db;
use Client\Commands\Env;
use Client\Commands\Phinx;
use Client\Errors\ClientError;
use Client\Errors\CommandError;
use Client\Errors\FileError;
use Client\Errors\TransactionError;
use Client\Errors\ValidatorError;
use Client\Traits\Config;
use Client\Traits\General;
use Symfony\Component\Filesystem\Exception\IOException;

class Installer {

	use Config;

	use General;

	const UNZIP_FOLDER_NAME = 'latest';

    public function __construct(array $config = array()) {
        $this->setConfig($config);
    }

	/**
	 * Update .
	 *
	 */
	public function update() {
		$warnings = array();

		try {
			monolog('Booting update');

			monolog('Current application version {version}', ['version' => env('APP_VERSION')]);

			$response = http('version/check', array(
                'params' => array(
                    'client_version' => env('APP_VERSION')
                )
            ));

			if(! $response->isValid())
			    throw new ClientError( $response->getMessage() );

            $response = http('version/latest');

            if(! $response->isValid())
                throw new ClientError( $response->getMessage() );

            $data = $response->getConfig('data');

			$requirements = isset($data['requirements'])
				? (array)$data['requirements']
				: array();

			$requirements = array_merge($requirements, array(
				'db:db', 'write:write'
			));

			monolog('Check for required extensions: {ext}', ['ext' => json_encode($requirements)]);

			$errors = $this->checkValidators($requirements);

			if( $errors )
				throw new ValidatorError( array_pop($errors) );

			monolog('Extensions OK');

			monolog('Cleaning temporary directory');

			$this->cleanTempDir();

			monolog('Downloading latest version: {version}', ['version' => isset($data['version']) ? $data['version'] : null]);

			$file = http('version/download', array(
				'params' => array(
					'client_version' => env('APP_VERSION')
				),
				'to_file' => $this->getTempDir() . DIRECTORY_SEPARATOR . self::UNZIP_FOLDER_NAME .'.zip'
			));

			if(! $file->isValid()) {
				throw new ClientError('Invalid patch downloaded. Cannot unzip.');
			}

			$path = $file->unzip( $this->getTempDir( self::UNZIP_FOLDER_NAME ) );

			$manifest_path = $path . DIRECTORY_SEPARATOR . 'manifest.json';

			if(! filesystem()->exists($manifest_path))
				throw new FileError('Cannot locate manifest file.');

			$manifest = new Manifest(
                $manifest_path
			);

			monolog('Backup server files::');

			if(! $this->backupFiles( $manifest->getFiles() ))
				throw new ClientError('Error on backup files');

			try {
				monolog('Enable maintenance mode');

				(new Env( ROOT_PATH ))
					->enableMaintenance()
                    ->save();

				monolog('Disable jobs');

				(new Env( ROOT_PATH ))
					->disableJobs()
                    ->save();

			} catch (CommandError $e) {
				$warnings[] = $e->getMessage();
			}

			/** Start copy files ... */

			try {

				monolog('Start copy patch files::');

				$has_migrations = false;
                $has_composer = false;

				/** @var Copy patch files to original rewriting ... $file */
				foreach ($manifest->getFiles() as $obj) {

					if(! isset($obj['file']))  {

						monolog('Missed patch file. Skipping ...');

						continue;
					}

					if( $obj['file'] == 'composer.json' )
                        $has_composer = true;

                    if( preg_match_all('/migration/i', $obj['file']) )
                        $has_migrations = true;

                    if(! isset($obj['mode']))
						$obj['mode'] = 'copy';

					$patch_file = $this->getTempDir( self::UNZIP_FOLDER_NAME ) . DIRECTORY_SEPARATOR . $obj['file'];

					if(! filesystem()->exists($patch_file))
						continue;

					$original_path_file = ROOT_PATH . DIRECTORY_SEPARATOR . $obj['file'];

					if( $obj['mode'] == 'copy' ) {

                        if( $obj['file'] == '.env.example' ) {
                            $sync = (new Env( ROOT_PATH, '.env.example' ))
                                ->toArray();

                            $local = (new Env( ROOT_PATH, '.env' ))
                                ->toArray();

                            if( $env_diff = array_diff_key($sync, $local) ) {
                                monolog('Update .env file with new keys: {keys}', ['keys' => implode(',', array_keys($env_diff))]);

                                (new Env( ROOT_PATH, '.env' ))
                                    ->save((array)$env_diff);
                            }
                        }

						$path = pathinfo( $obj['file'] );

						$path = ROOT_PATH . DIRECTORY_SEPARATOR . $path['dirname'];

						if(! filesystem()->exists( $path ) )
						    filesystem()->mkdir($path);

						try {
                            filesystem()->copy( $patch_file,  $original_path_file, true);

                            monolog(' --[{file}] ... copied', ['file' => $original_path_file]);

                        } catch (IOException $e) {
						    continue;
                        }

					} elseif ( $obj['mode'] == 'delete' ) {
						if( filesystem()->exists($original_path_file) ) {

						    filesystem()->remove($original_path_file);

							monolog(' --[{file}] ... deleted', ['file' => $original_path_file]);
						} else {
							monolog(' --[{file}] ... not found, cannot delete', ['file' => $original_path_file]);

						}
					}
				}

			} catch (TransactionError $error) {

				monolog("[exception:{class}:{file}:{line}] {message}", ['class' => get_class($error), 'file' => $error->getFile(), 'line' => $error->getLine(), 'message' => $error->getMessage() ]);

				monolog('Start restore files::');

				$this->restoreFiles();

				try {
					monolog('Disable maintenance mode');

					(new Env( ROOT_PATH ))
						->disableMaintenance()
                        ->save();

					monolog('Enable jobs');

					(new Env( ROOT_PATH ))
						->enableJobs()
                        ->save();

				} catch (CommandError $e) {
					$warnings[] = $e->getMessage();
				}

				throw new ClientError($error->getMessage());
			}

			/** End backup files */

			try {

				(new Env( ROOT_PATH ))
					->version( $manifest->getVersion() )
                    ->save();

				monolog('Update version file to {version}', ['version' => $manifest->getVersion()]);

				(new Env( ROOT_PATH ))
					->disableMaintenance()
                    ->save();

				monolog('Disabled maintenance mode');

				(new Env( ROOT_PATH ))
					->enableJobs()
                    ->save();

                (new Cache( ROOT_PATH ))
                    ->clean();

				monolog('Enable jobs');

				if( $this->isBackupDbEnabled() ) {

					monolog('Backup database');

					(new Db( ROOT_PATH ))
						->backup( $this->getBackDbPath() );
				}

				if( $has_migrations ) {
                    (new Phinx( ROOT_PATH ))
                        ->migrate();

                    monolog('Migrated database');
                }

				if( $has_composer ) {
                    (new Composer)
                        ->update();

                    monolog('Composer updated');
                }

			} catch (CommandError $e) {
				$warnings[] = $e->getMessage();
			}

			foreach ($warnings as $warning)
				monolog("[warning] {message}", ['message' => $warning]);

		} catch (ClientError $error) {
            monolog("[exception:{class}:{file}:{line}] {message}", ['class' => get_class($error), 'file' => $error->getFile(), 'line' => $error->getLine(), 'message' => $error->getMessage() ]);

			monolog('Cleaning temporary directory');

			$this->cleanTempDir();

			if( $warnings ) {
				foreach ($warnings as $warning) {
					flashMessage($warning, FlashMessage::WARNING);
				}
			}

			monolog('Exit');

			throw $error;
		}

		if( $has_migrations || $has_composer ) {
            (new Manifest( $this->getBackupPath() . DIRECTORY_SEPARATOR . 'manifest.json' ))
                ->addConfig('has_migrations', $has_migrations)
                ->addConfig('has_composer', $has_composer)
                ->save();
        }

		if( $warnings ) {
			foreach ($warnings as $warning) {
				flashMessage($warning, FlashMessage::WARNING);
			}
		}

		monolog('Cleaning temporary directory');

		$this->cleanTempDir( $this->getTempDir() . '*.zip' );

		monolog('Done');
	}

	/**
	 * Revert to latest before update version
	 */
	public function revert() {
		$warnings = array();

		try {
			monolog('Booting update');

			monolog('Current application version: {version}', ['version' => env('APP_VERSION')]);

			if(! $this->canDoRevert() )
				throw new ClientError('Cannot revert. Cannot fount latest backup');

			$manifest = new Manifest(
                $this->getBackupPath() . DIRECTORY_SEPARATOR . 'manifest.json'
			);

			try {
				monolog('Enable maintenance mode');

				(new Env( ROOT_PATH ))
					->enableMaintenance()
                    ->save();

				monolog('Disable jobs');

				(new Env( ROOT_PATH ))
					->disableJobs()
                    ->save();

                monolog('Rollback migrations');

                if( $manifest->getConfig('has_migrations') === true ) {
                    (new Phinx( ROOT_PATH ))
                        ->rollback();
                }

			} catch (CommandError $e) {
				$warnings[] = $e->getMessage();
			}

			/** Start restore files ... */

			monolog('Start restore files::');

			$this->restoreFiles();

			try {

                monolog('Update version file to {version}', ['version' => $manifest->getVersion()]);

                (new Env( ROOT_PATH ))
					->version( $manifest->getVersion() )
                    ->save();

                monolog('Disable maintenance mode');

                (new Env( ROOT_PATH ))
					->disableMaintenance()
                    ->save();

                monolog('Enable jobs');

                (new Env( ROOT_PATH ))
					->enableJobs()
                    ->save();

                if( $manifest->getConfig('has_composer') === true ) {
                    monolog('Composer update');

                    (new Composer)->update();
                }

            } catch (CommandError $e) {
				$warnings[] = $e->getMessage();
			}

			foreach ($warnings as $warning)
				monolog("[warning] {message}", ['message' => $warning]);


		} catch (ClientError $error) {
            monolog("[exception:{class}:{file}:{line}] {message}", ['class' => get_class($error), 'file' => $error->getFile(), 'line' => $error->getLine(), 'message' => $error->getMessage() ]);

            foreach ((array)$warnings as $warning) {
                flashMessage($warning, FlashMessage::WARNING);
            }

            monolog('Exit');

			throw $error;
		}

		if( $warnings ) {
			foreach ($warnings as $warning) {
				flashMessage($warning, FlashMessage::WARNING);
			}
		}

		monolog('Cleaning temporary directory');

		$this->cleanTempDir();

		monolog('Done');
	}

	/**
	 * Check if user can do revert .
	 *
	 * @return bool
	 */
	public function canDoRevert() {
		return is_dir( $this->getTempDir( 'backup' )) &&
            filesystem()->exists( $this->getBackupPath() . DIRECTORY_SEPARATOR . 'manifest.json' );
	}


	/**
	 * Check requirements .
	 *
	 * @param array $requirements
	 * @return array
	 */
	protected function checkValidators(array $requirements) {
		$errors = array();

		foreach ($requirements as $requirement) {
			$validator = false;

			$parts = explode(':', $requirement);

			if(! $parts)
				continue;

			foreach ($this->getValidators() as $validator => $tag)
				if( $tag == $parts[0] )
					break;

			if(! $validator)
				continue;

			$validator = (new $validator($parts[1]));

			if(! $validator->isValid())
				$errors[$requirement] = $validator->getErrorMessage();
		}

		return $errors;
	}

	/**
	 * Backup files
	 *
	 * @param array $files
	 * @return bool
	 */
	protected function backupFiles(array $files) {
		$files = (array)$files;

		foreach ($files as $obj) {

			if(! isset($obj['file']))
			    continue;

			$original = ROOT_PATH . DIRECTORY_SEPARATOR . $obj['file'];

			if( ! filesystem()->exists($original) ) {

				monolog(' --[{file}] ... cannot find!', ['file' => $original]);

				continue;
			}

			$path = $this->getBackupPath() . DIRECTORY_SEPARATOR . $obj['file'];

			try {
                filesystem()->copy( $original, $path, true );

                monolog(' --[{file}] ... copied', ['file' => $original]);

            } catch (IOException $e) {
                monolog(' --[{file}] ... cannot copy!', ['file' => $original]);
            }

		}

		file_put_contents( $this->getBackupPath() . DIRECTORY_SEPARATOR . 'manifest.json', json_encode(array(
			'version' => env('APP_VERSION'),
			'files' => $files
		)) );

		return true;
	}

	/**
	 * Restore files in case of error .
	 *
	 * @return bool
	 */
	protected function restoreFiles() {
		$manifest = new Manifest(
            $this->getBackupPath() . DIRECTORY_SEPARATOR . 'manifest.json'
		);

		foreach ($manifest->getFiles() as $obj) {
			$original = $this->getBackupPath() . DIRECTORY_SEPARATOR . $obj['file'];

			if(! filesystem()->exists($original))
				continue;

			try {
                filesystem()->copy( $original, ROOT_PATH . DIRECTORY_SEPARATOR . $obj['file'], true );

                monolog(' --[{file}] ... restored', ['file' => $original]);
            } catch (IOException $e) {
                monolog(' --[{file}] ... cannot copy!', ['file' => $original]);
            }
		}

		return true;
	}

}
