<?php

namespace Client\Request\Auth;

interface Authenticable {

	/**
	 * Authenticate resource
	 *
	 * @param $resource
	 * @return mixed
	 */
	public function authenticate($resource);

}