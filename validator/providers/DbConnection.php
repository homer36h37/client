<?php

namespace Client\Validator\Providers;

use Client\Validator\ValidateAble;
use Client\Validator\Validator;
use mysqli;

class DbConnection extends Validator
	implements ValidateAble {

	protected $message = 'No connection to %s';

	/**
	 * Check if check is valid .
	 *
	 * @return bool
	 */
	public function isValid() {
		$mysqli = new mysqli(
			env('DB_HOST'),
			env('DB_USERNAME'),
			env('DB_PASSWORD'),
			env('DB_DATABASE'),
			env('DB_PORT')
		);

		return ! $mysqli->connect_errno;
	}
}