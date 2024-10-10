<?php

namespace Client\Validator;

abstract class Validator {

	private $expression;

	protected $message = 'Invalid %s';

	public function __construct($expression) {
		$this->expression = $expression;
	}

	/**
	 * @return mixed
	 */
	public function getExpression() {
		return $this->expression;
	}

	/**
	 * Get error message .
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		return sprintf( $this->message, $this->getExpression() );
	}
}