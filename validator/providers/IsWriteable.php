<?php

namespace Client\Validator\Providers;

use Client\Validator\ValidateAble;
use Client\Validator\Validator;

class IsWriteable extends Validator
	implements ValidateAble {

	/**
	 * Check if check is valid .
	 *
	 * @return bool
	 */
	public function isValid() {
		return is_writable( ROOT_PATH );
	}

}