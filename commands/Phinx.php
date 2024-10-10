<?php

namespace Client\Commands;

use Client\Command;
use Client\Errors\CommandError;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Phinx extends Command {

    /**
     * Migrate database ..
     *
     * @throws CommandError
     */
    public function migrate() {
        $this->execute('migrate');
    }

    /**
     * Seed database ..
     *
     */
    public function seed() {
        $this->execute('seed:run -s Runner');
    }

    /**
     * Rollback to the last migration .
     *
     */
    public function rollback() {
        $this->execute('rollback');
    }


    /**
     * Execute command
     *
     * @param $command
     * @return mixed
     * @throws CommandError
     */
    protected function execute($command) {
        $config_found    = false;
        $found_extension = 'php';

        foreach (array('php', 'yml', 'json') as $extension) {
            if(filesystem()->exists( sprintf('%s/phinx.%s', $this->path , $extension) )) {
                $config_found = true;
                $found_extension = $extension;
                break;
            }
        }

        if(! $config_found)
            throw new CommandError('Cannot locale phinx config file');

        if(! filesystem()->exists( $this->path . '/vendor/bin/phinx' ))
            throw new CommandError('Cannot locate phinx');

        try {
            $process = (new \Symfony\Component\Process\Process(
                sprintf("%s %s {$command} -c %s",
                    (new \Symfony\Component\Process\PhpExecutableFinder())->find(),
                    $this->path . '/vendor/bin/phinx',
                    $this->path ."/phinx.{$found_extension}"
                ), $this->path
            ))->mustRun();

            $output = $process->getOutput();

            monolog($output);

            return $output;

        } catch (ProcessFailedException $e) {
            monolog($e->getMessage());

            throw new CommandError('Db execute error');
        }
    }

}
