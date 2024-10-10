<?php

namespace Client\Request;

use Client\Errors\RequestError;
use Client\Request\Auth\Authenticable;

class HttpRequest implements RequestAble {

	const URL_LOCAL = 'http://localhost:80/api';

	const URL_DEV  = 'http://dev.kidlogger.net/api';

	const URL_LIVE  = 'http://kidlogger.net/api';

	const TIMEOUT = 3600;

	protected $handle;

	public $headers = array();

	public $params = array();

	public $post = array();

	public $files = array();

	protected $authenticable;

	public function __construct() {
		$this->handle = curl_init();
	}

	/**
	 * @return resource
	 */
	public function getHandle() {
		return $this->handle;
	}


	/**
	 * Add header key / value pair
	 *
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public function addHeader($key, $value) {
		$this->headers[$key] = $value;

		return $this;
	}

	/**
	 * Add new headers ..
	 *
	 * @param array $headers
	 * @return mixed
	 */
	public function addHeaders(array $headers) {
		$this->headers = array_merge($headers, $this->headers);

		return $this;
	}


	/**
	 * Add new post key / value pair
	 *
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public function addPost($key, $value) {
		$this->post[$key] = $value;

		return $this;
	}

	/**
	 * Set post .
	 *
	 * @param array $posts
	 * @return mixed
	 * @internal param array $post
	 */
	public function addPosts(array $posts) {
		$this->post = array_merge($posts, $this->post);

		return $this;
	}


	/**
	 * Add param key / value pair
	 *
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public function addParam($key, $value) {
		$this->params[$key] = $value;

		return $this;
	}

	/**
	 * Add params .
	 *
	 * @param array $params
	 * @return mixed
	 */
	public function addParams(array $params) {
		$this->params = array_merge($params, $this->params);

		return $this;
	}


	/**
	 * Add file key / value pair
	 *
	 * @param $file
	 * @param $path
	 * @return mixed
	 */
	public function addFile($file, $path) {
		$this->files[$file] = $path;

		return $this;
	}

	/**
	 * Add files .
	 *
	 * @param array $files
	 * @return mixed
	 */
	public function addFiles(array $files) {
		$this->files = array_merge($files, $this->files);

		return $this;
	}


	/**
	 * @param $path
	 * @param array $options
	 * @return File|Response|mixed
	 * @throws RequestError
	 */
	public function execute($path, array $options) {
		$handler = $this->getHandle();

		if( $this->authenticable && $this->authenticable instanceof Authenticable )
			if( $headersAuth = $this->authenticable->authenticate( $handler ))
				$this->addHeaders(array($headersAuth));

		if( $headers = $this->headers ) {}
			curl_setopt($handler, CURLOPT_HTTPHEADER, (array)$headers);

		$query = $this->params
			? '?' . http_build_query($this->params)
			: '';

		if( $files = $this->files ) {
			$pre_post = array();

			curl_setopt($handler, CURLOPT_SAFE_UPLOAD, true);

			foreach ($files as $file => $file_path) {
				$pre_post[$file] = ( version_compare(PHP_VERSION, '5.5') >= 0 )
					? new \CURLFile($file_path)
					: '@' . $file_path;
			}

			$this->addPosts($pre_post);
		}

		if( $post = $this->post ) {
			curl_setopt($handler, CURLOPT_POST, true);
			curl_setopt($handler, CURLOPT_POSTFIELDS, $post);
		}

		$file = null;
		if( isset($options['to_file']) ) {
			$file = fopen( $options['to_file'], 'w+');

			curl_setopt($handler, CURLOPT_FILE, $file);
			curl_setopt($handler, CURLOPT_FOLLOWLOCATION, true);
		}

		if(! isset($options['to_file']))
			curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);

		if( env('APPLICATION_ENV') == 'development' ) {
			$url = self::URL_DEV;
		} elseif ( env('APPLICATION_ENV') == 'local' ) {
			$url = self::URL_LOCAL;
		} else {
			$url = self::URL_LIVE;
		}

        curl_setopt($handler, CURLOPT_URL, $url . DIRECTORY_SEPARATOR . $path . $query);
		curl_setopt($handler, CURLOPT_TIMEOUT, self::TIMEOUT);

		$response = curl_exec($handler);

		if( ! isset($options['to_file']) ) {
			$response = json_decode($response, true);

			$response = curl_errno($handler)
				? array(
					'status' => false,
					'status_code' => 500,
					'message' => curl_error($handler)
				)
				: $response;

			if(! is_array($response))
				throw new RequestError('Invalid response');

			return new HttpResponse($response);

		}

		fclose($file);

		return new File( $options['to_file'] );
	}

	/**
	 * Authenticate resource .
	 *
	 * @param Authenticable $handler
	 * @return $this
	 */
	public function authenticate(Authenticable $handler) {
		$this->authenticable = $handler;

		return $this;
	}


}