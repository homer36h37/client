<?php

namespace Client;

abstract class Command {

    /**
     * @var
     */
    protected $path;

    public function __construct($path) {
        $this->path = $path;
    }
}