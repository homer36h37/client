<?php

class FileTest extends TestCase {

	public function testShouldReturnPath() {
		$path = $this->getStoragePath('patch.zip');

		$file = new Client\Request\File($path);

		$this->assertEquals( $path, $file->getPath() );
	}

	public function testIsValidShouldReturnFalseIfFileNotFound() {
		$path = $this->getStoragePath('invalid_file.zip');

		$file = new Client\Request\File($path);

		$this->assertFalse( $file->isValid() );
	}

	public function testIsValidShouldReturnTrueIfFileFound() {
		$path = $this->getStoragePath('patch.zip');

		$file = new Client\Request\File($path);

		$this->assertTrue( $file->isValid() );
	}

	public function testShouldUnzipFile() {
		$path = $this->getStoragePath('patch.zip');

		$file = new Client\Request\File($path);

		$extract_to = $this->getStoragePath('patch');

		$file->unzip( $extract_to );

		$this->assertTrue( is_dir( $extract_to ) );

		$this->recursiveRemoveDirectory($extract_to);

	}

	public function testUnzipShouldReturnPath() {
		$path = $this->getStoragePath('patch.zip');

		$file = new Client\Request\File($path);

		$extract_to = $this->getStoragePath('patch');

		$return_path = $file->unzip( $extract_to );

		$this->assertEquals( $return_path, $extract_to );

		$this->recursiveRemoveDirectory($extract_to);
	}

}