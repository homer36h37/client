<?php

namespace Client\Traits;

use Client\Errors\ClientError;
use Client\Request\Auth\Handlers\Token;
use Client\Request\HttpRequest;

trait General {

	/**
	 * Get backup path
	 *
	 * @return array
	 */
	public function getBackupPath() {
		return $this->getConfig('backup_path', dirname(__FILE__) . '/storage/temp_dir/backup/');
	}

	/**
	 * Get temporary dir .
	 *
	 * @param null $path
	 * @return array
	 */
	public function getTempDir($path = null) {
		$full = $this->getConfig('temp_dir', dirname(__FILE__) . '/storage/temp_dir/');

		return $path
			? $full . DIRECTORY_SEPARATOR . $path
			: $full;
	}


	/**
	 * Check if backup is enabled
	 *
	 * @return bool
	 */
	public function isBackupDbEnabled() {
		$db = $this->getConfig('db');

		return isset($db['backup'])
			? $db['backup']
			: false;
	}

	/**
	 * Get backup db path .
	 *
	 * @return null
	 */
	public function getBackDbPath() {
		$db = $this->getConfig('db');

		return isset($db['backup_db_path'])
			? $db['backup_db_path']
			: null;
	}

	/**
	 * Get all backup files .
	 *
	 * @return array
	 */
	public function getBackupDbFiles() {
		return glob( $this->getBackDbPath() . '*.sql' );
	}


	/**
	 * Get registered validators .
	 *
	 * @return array
	 */
	public function getValidators() {
		return $this->getConfig('validators', array());
	}

	/**
	 * Clean temporary dir .
	 *
	 * @param null $path
	 * @return $this
	 */
	public function cleanTempDir( $path = null ) {
		$files = $path
			? glob($path, GLOB_MARK|GLOB_BRACE)
			: glob( $this->getTempDir() . '{,.}[!.,!..]*', GLOB_MARK|GLOB_BRACE);

		foreach($files as $file) {
			if( filesystem()->exists( $file ) ) {
				$this->cleanTempDir( $file . '{,.}[!.,!..]*');

				filesystem()->remove($file);
			}

			if(is_file($file)) {
				filesystem()->remove($file);
			}
		}

		return $this;
	}

}