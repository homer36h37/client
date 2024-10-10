<?php

namespace Client;

class FlashMessage {

	const SUCCESS = 'success';

	const WARNING = 'warning';

	const ERROR = 'error';

	protected $template;

	public function __construct() {
		if(! session_id())
			session_start();
	}


	/**
	 * Put message
	 *
	 * @param $message
	 * @param string $type
	 * @return $this
	 */
	public function put($message, $type = self::SUCCESS) {
		if(! isset($_SESSION[$type]))
			$_SESSION[$type] = array();

		$_SESSION[$type][] = $message;

		return $this;
	}

	/**
	 * Pull message
	 *
	 * @param string $type
	 * @return mixed|void
	 */
	public function pull($type = self::SUCCESS) {
		if(! isset($_SESSION[$type]))
			return;

		return array_pop($_SESSION[$type]);
	}


	/**
	 * Flush messages .
	 *
	 * @param null $type
	 * @return $this
	 */
	public function flush($type = null) {
		$types = ! is_null($type)
			? (array)$type
			: array(self::SUCCESS, self::WARNING, self::ERROR);

		foreach ($types as $type)
			unset($_SESSION[$type]);

		return $this;
	}


	/**
	 * Set flash template
	 *
	 * @param $template
	 * @return $this
	 */
	public function setTemplate( $template ) {
		$this->template = $template;

		return $this;
	}

	/**
	 * Get flash template
	 * @param array $variables
	 * @return mixed
	 */
	public function getTemplate(array $variables) {
		$template = $this->template;

		foreach ($variables as $key => $variable)
			$template = str_replace($key, $variable, $template);

		return $template;
	}


	/**
	 * Render messages by type or all
	 *
	 * @param null $type
	 * @return string
	 */
	public function render( $type = null ) {
		$types = ! is_null($type)
			? (array)$type
			: array(self::SUCCESS, self::WARNING, self::ERROR);

		$html = "<script>";
		foreach ($types as $type) {
			while( $message = $this->pull( $type ) ) {
				$html .= $this->getTemplate(array(
					'%message%' => $message,
					'%type%' => $type
				));
			}
		}

		$html .= "</script>";

		$this->flush( $types );

		return $html;
	}

	/**
	 * Render template .
	 */
	public function __toString() {
		echo $this->render();
	}
}