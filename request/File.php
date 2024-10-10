<?php

namespace Client\Request;

use Client\Errors\FileError;
use ZipArchive;

class File {

	/** @var   */
	private $path;

	public function __construct($path) {
		$this->path = $path;
	}

	/**
	 * @return mixed
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Check if file is valid .
	 *
	 * @return bool
	 */
	public function isValid() {
		return filesystem()->exists( $this->getPath() ) && ( (new ZipArchive())->open( $this->getPath(), ZipArchive::CHECKCONS ) );
	}

	/**
	 * Unzip archive .
	 *
	 * @param string $export_to_path
	 * @return string
	 * @throws FileError
	 */
	public function unzip($export_to_path) {
		if(! $this->isValid())
			throw new FileError('Invalid archive');

		$zip = new ZipArchive;

		if(! $zip->open( $this->getPath() ))
			throw new FileError('Error on extracting.');

		$dirname = pathinfo($export_to_path)['dirname'];

		if(! filesystem()->exists($dirname))
			filesystem()->mkdir($dirname);

		$zip->extractTo($export_to_path);
		$zip->close();

		return $export_to_path;
	}

}