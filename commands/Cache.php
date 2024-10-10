<?php

namespace Client\Commands;

use Client\Command;
use Zend_Cache;

class Cache extends Command {

    /**
     * Clean default cache .
     *
     */
    public function clean() {
        $cache = Zend_Cache::factory('Core', 'File', array(
            'automatic_serialization' => true,
            'lifetime' => 3600 * 24 * 365
        ), array(
            'cache_dir' => CACHE_PATH
        ));

        $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
    }
}
