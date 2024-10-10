<?php

namespace Client\Request;

interface RequestAble {

	/**
	 * Add header key / value pair
	 *
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public function addHeader($key, $value);

	/**
	 * Add new headers ..
	 *
	 * @param array $headers
	 * @return mixed
	 */
	public function addHeaders(array $headers);


	/**
	 * Add new post key / value pair
	 *
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public function addPost($key, $value);

	/**
	 * Set post .
	 *
	 * @param array $post
	 * @return mixed
	 */
	public function addPosts(array $post);


	/**
	 * Add param key / value pair
	 *
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public function addParam($key, $value);

	/**
	 * Add params .
	 *
	 * @param array $params
	 * @return mixed
	 */
	public function addParams(array $params);


	/**
	 * Add file key / value pair
	 *
	 * @param $file
	 * @param $path
	 * @return mixed
	 */
	public function addFile($file, $path);

	/**
	 * Add files .
	 *
	 * @param array $files
	 * @return mixed
	 */
	public function addFiles(array $files);


	/**
	 * @param $path
	 * @param array $options
	 * @return mixed
	 */
	public function execute($path, array $options);
}