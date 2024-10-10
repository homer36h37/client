<?php

namespace Client;

use Client\Traits\Config;

class Manifest {

	use Config;

    private $path;

    public function __construct($path) {
        $params = json_decode(file_get_contents( $path ), true);

		$this->setConfig($params);
        $this->path = $path;
    }

	/**
	 * Get updated files ..
	 *
	 * @return mixed|null
	 */
	public function getFiles() {
		return $this->getConfig('files', array());
	}

	/**
	 * Get requirements
	 *
	 * @return array
	 */
	public function getRequirements() {
		return $this->getConfig('requirements');
	}

	/**
	 * Get version .
	 *
	 * @return array
	 */
	public function getVersion() {
		return $this->getConfig('version');
	}

	/**
	 * Check if need to update composer .
	 *
	 * @return array
	 */
	public function hasComposerUpdate() {
		return $this->getConfig('has_composer_update', false);
	}

    /**
     * Save current configurations
     */
	public function save() {
	    filesystem()->dumpFile(
	        $this->path, json_encode( $this->toArray() )
        );

	    return $this;
    }
}