<?php

namespace Client\Validator\Providers;

use Client\Validator\ValidateAble;
use Client\Validator\Validator;

class PhpExtChecker extends Validator
	implements ValidateAble {

	protected $message = 'No extension found %s';


	/**
	 * Check if check is valid .
	 *
	 * @return bool
	 */
	public function isValid() {
		return extension_loaded($this->getExpression());
	}
}