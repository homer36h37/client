<?php

class TestCase extends PHPUnit_Framework_TestCase {

	protected $storagePath;

	public function setUp() {
		parent::setUp();

		$this->setStoragePath(
			dirname(__FILE__) . DIRECTORY_SEPARATOR . 'storage'
		);
	}

	/**
	 * Set storage path
	 *
	 * @param $path
	 * @return $this
	 */
	protected function setStoragePath($path) {
		$this->storagePath = $path;

		return $this;
	}

	/**
	 * Get storage path
	 * @param $relative
	 * @return string
	 */
	protected function getStoragePath($relative = null) {
		return $this->storagePath . DIRECTORY_SEPARATOR . $relative;
	}

	/**
	 * Remove recursive dir .
	 *
	 * @param $directory
	 */
	protected function recursiveRemoveDirectory($directory) {
		foreach (glob("{$directory}/*") as $file) {
			if (is_dir($file)) {
				$this->recursiveRemoveDirectory($file);
			} else {
				unlink($file);
			}
		}

		rmdir($directory);
	}
}