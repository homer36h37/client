<?php

class InstallerTest extends TestCase {

	public function testShouldInstallParams() {
		$installer = new \Client\Installer(
			require $this->getStoragePath('config_test.php')
		);

		$this->assertNotEmpty( $installer->getConfigs() );
	}

	public function testShouldSetEnvVariables() {
		$installer = new \Client\Installer(
			require $this->getStoragePath('config_test.php')
		);

		$installer->setEnv(array(
			'my_test_env' => 'my_test_env_value'
		));

		$this->assertNotEmpty( $installer->getEnv('my_test_env') );
	}

	public function testShouldReturnBackUpPath() {
		$installer = new \Client\Installer(
			require $this->getStoragePath('config_test.php')
		);

		$this->assertNotEmpty( $installer->getBackupPath() );
	}

	public function testShouldReturnTemporaryDir() {
		$installer = new \Client\Installer(
			require $this->getStoragePath('config_test.php')
		);

		$this->assertNotEmpty( $installer->getTempDir() );
	}

	public function testShouldReturnLogFile() {
		$installer = new \Client\Installer(
			require $this->getStoragePath('config_test.php')
		);

		$this->assertNotEmpty( $installer->getLogFile() );
	}

	public function testShouldBeInstalledValidators() {
		$installer = new \Client\Installer(
			require $this->getStoragePath('config_test.php')
		);

		$this->assertNotEmpty( $installer->getValidators() );
	}

	public function testShouldReturnArrayValidators() {
		$installer = new \Client\Installer(
			require $this->getStoragePath('config_test.php')
		);

		$this->assertTrue( is_array($installer->getValidators()) );
	}

	public function testShouldCleanTemporaryDir() {
		$installer = new \Client\Installer(
			require $this->getStoragePath('config_test.php')
		);

		$temp_dir = $installer->getTempDir();

		if( ! is_dir( $temp_dir ))
			mkdir( $temp_dir , 0777, true );

		touch($temp_dir . DIRECTORY_SEPARATOR . 'test.txt');

		$installer->cleanTempDir();

		$this->assertTrue( !filesystem()->exists( $temp_dir . DIRECTORY_SEPARATOR . 'test.txt' ) );
	}

	public function testShouldCreateLogFile() {
		$installer = new \Client\Installer(
			require $this->getStoragePath('config_test.php')
		);

		$this->assertTrue( filesystem()->exists( $installer->getLogFile() ) );
	}

}