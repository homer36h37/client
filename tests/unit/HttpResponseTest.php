<?php

class HttpResponseTest extends PHPUnit_Framework_TestCase {

	protected $response;

	public function setUp() {
		$this->response = new \Client\Request\HttpResponse(array(
			'status' => true,
			'status_code' => 200,
			'message' => 'This is a test message',
		));
	}

	public function testShouldReturnTrueIfValid() {
		$this->assertTrue( $this->response->isValid() );
	}

	public function testShouldReturnMessage() {
		$this->assertNotNull( $this->response->getMessage() );
	}

	public function testShouldReturnHttpCode() {
		$this->assertNotNull( $this->response->getHttpCode() );

	}
}