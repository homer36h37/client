<?php

namespace Client\Traits;

trait Config {

	private $config;

    /**
	 * @return mixed
	 */
	public function getConfigs() {
		return $this->toArray();
	}

	/**
	 * @param $key
	 * @param null $default
	 * @return array
	 */
	public function getConfig($key, $default = null) {
		return isset($this->config[$key])
			? $this->config[$key]
			: $default;
	}

	/**
	 * @param array $config
	 */
	public function setConfig($config) {
		$this->config = $config;
	}

	/**
	 * Add new config .
	 *
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function addConfig($key, $value) {
		$this->config[$key] = $value;

		return $this;
	}

    /**
     * Return array of config .
     *
     * @return mixed
     */
	public function toArray() {
	    return $this->config;
    }

}