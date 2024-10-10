<?php

use Client\Request\Auth\Handlers\Token;
use Symfony\Component\Filesystem\Filesystem;

if(! function_exists('monolog')) {

    function monolog($message = null, array $context = array(), $level = 'debug') {
        global $logger;

        if(! $message)
            return $logger;

        return call_user_func_array(array($logger, 'log'), array($message, $context, $level));
    }
}

if(! function_exists('flashMessage')) {

    function flashMessage($message, $type = \Client\FlashMessage::SUCCESS) {
        global $flushMessage;

        return $flushMessage->put($message, $type);
    }
}

if(! function_exists('http')) {

    function http($path, array $params = array()) {
        $request = new \Client\Request\HttpRequest();

        if( isset($params['headers']) )
            $request->addHeaders((array)$params['headers']);

        if( isset($params['post']) )
            $request->addPosts((array)$params['post']);

        if( isset($params['params']) )
            $request->addParams((array)$params['params']);

        if( isset($params['files']) )
            $request->addFiles((array)$params['files']);

        $options = array();
        if( isset($params['to_file']) )
            $options['to_file'] = $params['to_file'];

        $request->authenticate(
            new Token( env('APP_TOKEN') )
        );

        return $request->execute($path, $options);
    }
}

if(! function_exists('filesystem')) {

    function filesystem() {
         if(! isset($GLOBALS['filesystem'])) {
             $GLOBALS['filesystem'] = new Filesystem;
         }

         return $GLOBALS['filesystem'];
    }
}