<?php

namespace Client;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

class Log {

    /** @var  */
    protected $logger;

    /** @var   */
    private $channel;

    /** @var   */
    private $path;

    public function __construct( $channel, $path ) {
        $this->channel = $channel;
        $this->path = $path;
    }

    /**
     * Log message .
     *
     * @param $message
     * @param array|null $context
     * @param int|string $level
     * @return mixed
     */
    public function log($message, array $context = array(), $level = 'debug') {
        return call_user_func_array(array($this->logger(), $level), array($message, $context));
    }

    /**
     * Get log files .
     *
     * @return array
     */
    public function getLogFiles() {
        return glob( $this->path . '*.log' );
    }

    /**
     * Get logger instance .
     *
     */
    protected function logger() {
        if(! $this->logger) {
            $this->logger = (new Logger( $this->channel ))
                ->pushProcessor(new PsrLogMessageProcessor)
                ->pushHandler(
                    (new RotatingFileHandler( $this->path . DIRECTORY_SEPARATOR . 'debug.log' ))
                        ->setFormatter(
                            new LineFormatter(null, null, true)
                        )
                );
        }

        return $this->logger;
    }

    public function __call($name, $arguments) {
        return call_user_func_array(array($this->logger(), $name), $arguments);
    }
}