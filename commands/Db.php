<?php

namespace Client\Commands;

use Client\Command;
use Client\Errors\CommandError;
use Client\Validator\Providers\DbConnection;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Db extends Command {

    /**
     * Backup db .
     *
     * @param $path
     * @return mixed
     * @throws CommandError
     */
    public function backup( $path ) {
        if(! filesystem()->exists($path))
            filesystem()->mkdir($path);

        $filename = 'kidlogge_update_' . date('m_d_Y_H_i') . '.sql';

        if( filesystem()->exists( $path . DIRECTORY_SEPARATOR . $filename ) )
            filesystem()->remove( $path . DIRECTORY_SEPARATOR . $filename );

        try {
            $process = (new \Symfony\Component\Process\Process(
                sprintf("mysqldump -u %s -p%s %s > %s",
                    env('DB_USERNAME'),
                    env('DB_PASSWORD'),
                    env('DB_DATABASE'),
                    $path . DIRECTORY_SEPARATOR . $filename
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

    /**
     * Import db .
     *
     * @param $path
     * @return mixed
     * @throws CommandError
     */
    public function import( $path ) {
        try {
            if( ! filesystem()->exists( $path ) )
                throw new CommandError('Invalid file');

            $process = (new \Symfony\Component\Process\Process(
                sprintf("mysql -u %s -p%s %s < %s",
                    env('DB_USERNAME'),
                    env('DB_PASSWORD'),
                    env('DB_DATABASE'),
                    $path
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
