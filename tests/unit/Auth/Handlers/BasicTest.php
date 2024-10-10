<?php

use Client\Request\Auth\Handlers\Token;

class BasicTest extends PHPUnit_Framework_TestCase {

	public function testShouldReturnToken() {
		$handler = new Token( 'my_password' );

		$this->assertNotNull( $handler->getToken() );

		$this->assertEquals( $handler->getToken(), 'my_password' );
	}

	public function testAuthenticateShouldReturnString() {
		$handler = new Token( 'my_password' );

		$this->assertNotNull( $handler->authenticate('my_res') );
	}

	public function testAuthenticateShouldContainsAuthorization() {
		$handler = new Token( 'my_password' );

		$this->assertContains('Authorization', $handler->authenticate('my_res'));
	}

	public function testShouldImplementAuthenticable() {
		$handler = new Token( 'my_password' );

		$this->assertInstanceOf( \Client\Request\Auth\Authenticable::class, $handler );

	}
}