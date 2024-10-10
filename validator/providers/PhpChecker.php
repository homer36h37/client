<?php

namespace Client\Validator\Providers;

use Client\Validator\ValidateAble;
use Client\Validator\Validator;

class PhpChecker extends Validator
	implements ValidateAble {

	protected $message = 'No php found %s';


	/**
	 * Check if check is valid .
	 *
	 * @return bool
	 */
	public function isValid() {
		return version_compare(PHP_VERSION , $this->getExpression(), ">=");
	}
}