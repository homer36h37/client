<?php

class HttpRequestTest extends PHPUnit_Framework_TestCase {

	public function testShouldReturnHandler() {
		$request = new \Client\Request\HttpRequest();

		$this->assertNotNull( $request->getHandle() );
	}

	public function testShouldAddHeader() {
		$request = new \Client\Request\HttpRequest();

		$request->addHeader('test', 'test');

		$this->assertEquals( $request->headers['test'], 'test' );
	}

	public function testShouldAddHeaders() {
		$request = new \Client\Request\HttpRequest();

		$request->addHeaders(array(
			'test' => 'test'
		));

		$this->assertEquals( $request->headers['test'], 'test' );
	}

	public function testShouldAddPost() {
		$request = new \Client\Request\HttpRequest();

		$request->addPost('test', 'test');

		$this->assertEquals( $request->post['test'], 'test' );
	}

	public function testShouldAddPosts() {
		$request = new \Client\Request\HttpRequest();

		$request->addPosts(array(
			'test' => 'test'
		));

		$this->assertEquals( $request->post['test'], 'test' );
	}

	public function testShouldAddParam() {
		$request = new \Client\Request\HttpRequest();

		$request->addParam('test', 'test');

		$this->assertEquals( $request->params['test'], 'test' );
	}

	public function testShouldAddParams() {
		$request = new \Client\Request\HttpRequest();

		$request->addParams(array(
			'test' => 'test'
		));

		$this->assertEquals( $request->params['test'], 'test' );
	}

	public function testShouldAddFile() {
		$request = new \Client\Request\HttpRequest();

		$request->addFile('test', 'test');

		$this->assertEquals( $request->files['test'], 'test' );
	}

	public function testShouldAddFiles() {
		$request = new \Client\Request\HttpRequest();

		$request->addFiles(array(
			'test' => 'test'
		));

		$this->assertEquals( $request->files['test'], 'test' );
	}

	public function testConstUrlShouldReturnValidUrl() {
		$this->assertNotNull(\Client\Request\HttpRequest::URL);

		$this->assertTrue(
			filter_var( \Client\Request\HttpRequest::URL, FILTER_VALIDATE_URL) !== false
		);
	}

}