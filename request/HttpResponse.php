<?php

namespace Client\Request;

use Client\Traits\Config;

class HttpResponse {

	use Config;

	/**
	 * Response constructor.
	 * @param array $params
	 */
	public function __construct(array $params) {
		$this->setConfig($params);
	}

	/** To String . */
	public function __toString() {
		return json_encode( $this->getConfigs() );
	}

	/**
	 * Check if response is valid
	 *
	 * @return bool
	 */
	public function isValid() {
		return $this->getConfig('status') === true;
	}

	/**
	 * @return array
	 */
	public function getMessage() {
		return $this->getConfig('message');
	}

	/**
	 * Get http code
	 *
	 * @return array
	 */
	public function getHttpCode() {
		return $this->getConfig('status_code');
	}
}