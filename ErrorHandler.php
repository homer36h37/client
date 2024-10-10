<?php

namespace Client;

class ErrorHandler {

    /**
     * ErrorHandler constructor.
     * @param bool $isDebugMode
     */
    public function __construct($isDebugMode = true) {
        $this->isDebugMode = $isDebugMode;

        if( $isDebugMode ) {
            ini_set('display_startup_errors', 1);
            ini_set('display_errors', 1);
        } else {
            ini_set('display_startup_errors', 0);
            ini_set('display_errors', 0);
        }

        error_reporting(-1);

        set_error_handler(array($this, 'errorHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));
    }

    /** Error handler
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @return bool
     */
    public function errorHandler($errno, $errstr, $errfile, $errline) {
        $types = array(
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_STRICT => 'E_STRICT',
        );

        monolog(
            '[error] {type}: {message} "{file}:{line}"', array(
                'type' => isset($types[$errno]) ? $types[$errno] : 'E_ERROR',
                'message' => substr($errstr, 0, 266) . ' ...',
                'file' => $errfile,
                'line' => $errline,
            )
        );

        return true;
    }

    /**
     * Set exception error handler
     *
     * @param \Throwable $exception
     */
    public function exceptionHandler(\Throwable $exception) {
        monolog('[exception] {message} {file} {line}', array(
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ));
    }
}