<?php

namespace Client\Request\Auth\Handlers;

use Client\Request\Auth\Authenticable;

class Token implements Authenticable {

	/**
	 * @var
	 */
	private $token;

	public function __construct($token) {
		$this->token = $token;
	}

	/**
	 * Get token
	 *
	 * @return mixed
	 */
	public function getToken() {
		return $this->token;
	}

	/**
	 * Authenticate resource
	 *
	 * @param $resource
	 * @return mixed
	 */
	public function authenticate($resource) {
		return 'Authorization: Token ' . $this->getToken();
	}
}