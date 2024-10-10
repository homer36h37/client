<?php

class ManifestTest extends TestCase {

	public function testShouldReturnFiles() {
		$manifest = new \Client\Manifest(
			json_decode(file_get_contents( $this->getStoragePath('manifest.json') ), true)
		);

		$this->assertNotNull( $manifest->getFiles() );
	}

	public function testShouldReturnRequirements() {
		$manifest = new \Client\Manifest(
			json_decode(file_get_contents( $this->getStoragePath('manifest.json') ), true)
		);

		$this->assertNotNull( $manifest->getRequirements() );
	}

	public function testShouldReturnVersion() {
		$manifest = new \Client\Manifest(
			json_decode(file_get_contents( $this->getStoragePath('manifest.json') ), true)
		);

		$this->assertNotNull( $manifest->getVersion() );
	}
}