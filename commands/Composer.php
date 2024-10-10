<?php

namespace Client\Commands;

use Client\Command;
use Client\Errors\CommandError;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Composer {

	/**
	 * Composer update ..
	 *
	 * @return bool
	 * @throws CommandError
	 */
	public function update() {
		if(! filesystem()->exists( ROOT_PATH . DIRECTORY_SEPARATOR . 'composer.json' ))
			throw new CommandError('Cannot locate composer.json');

        if(! filesystem()->exists( ROOT_PATH . DIRECTORY_SEPARATOR . 'composer.phar' ))
            throw new CommandError('Cannot locate composer.phar');

		$composer_home = ROOT_PATH . DIRECTORY_SEPARATOR . 'var/cache/composer';

		if(! filesystem()->exists( $composer_home ))
			filesystem()->mkdir( $composer_home );

		putenv("COMPOSER_HOME={$composer_home}");
		putenv("COMPOSER_DISCARD_CHANGES=1");

        try {

            $process = (new \Symfony\Component\Process\Process(
                sprintf("%s %s update",
                    (new \Symfony\Component\Process\PhpExecutableFinder())->find(),
                    ROOT_PATH . DIRECTORY_SEPARATOR . 'composer.phar'
                ), ROOT_PATH, null, null, 450
            ))->mustRun(function ($err, $message) {
                if( !empty($message))
                    monolog('[' .$err . '] ' . $message);
            });

            return $process->getOutput();

        } catch (ProcessFailedException $e) {
            monolog( $e->getMessage() );

            throw new CommandError('Composer execute error');
        }
	}

}
