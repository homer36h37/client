<?php

namespace Client\Commands;

use Client\Command;
use Client\Errors\CommandError;

class Env extends Command {

    /** @var   */
    protected $data;

    /** @var   */
    protected $file;

    /**
     * @param $path
     * @param string $file
     * @throws CommandError
     */
    public function __construct($path, $file = '.env') {
        if(! filesystem()->exists( $path . DIRECTORY_SEPARATOR . $file ))
            throw new CommandError( sprintf('Cannot locate %s', $file) );

        $this->file = $path . DIRECTORY_SEPARATOR . $file;

        $this->data = parse_ini_file(
            $this->file, null, INI_SCANNER_RAW
        );
    }

    /**
     * Get element by key .
     *
     * @param $key
     * @param null $default
     * @return null
     */
    public function get($key, $default = null) {
        return isset($this->data[$key])
            ? $this->data[$key]
            : $default;
    }

    /**
     * Pull element by key
     *
     * @param $key
     * @return $this
     */
    public function pull($key) {
        if( isset($this->data[$key]) ) {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * Put new key
     *
     * @param $key
     * @param null $value
     * @return $this
     */
    public function put($key, $value = null) {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Store more values .
     *
     * @param array $values
     * @return $this
     */
    public function store(array $values) {
        foreach ($values as $key => $value) {
            $this->put($key, $value);
        }

        return $this;
    }

    /**
     * Save data to file .
     *
     * @param array $data
     * @return $this
     * @throws CommandError
     */
    public function save(array $data = array()) {
        if(! is_writable( $this->getFile() ))
            throw new CommandError(
                sprintf('File %s is not writeable', $this->getFile())
            );

        $this->data = array_merge(
            $this->data, $data
        );

        file_put_contents( $this->getFile(), $this->array2ini( $this->data ) );

        return $this;
    }

    /**
     * Return as array .
     *
     * @return array
     */
    public function toArray() {
        return $this->data;
    }


    /**
     * Get file .
     *
     * @return string
     */
    protected function getFile() {
        return $this->file;
    }

    /**
     * Array to ini
     *
     * @param array $data
     * @return string
     */
    protected function array2ini(array $data) {
        $out = '';

        foreach ($data as $key => $item)
            $out .= $key.'="'.$item .'"'. PHP_EOL;

        return $out;
    }


    /**
     * Transform value to type .
     *
     * @param $value
     * @return bool|string|void
     */
    public static function transform($value) {
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }

    /**
     * Update version
     *
     * @param $version
     * @return $this
     */
    public function version($version) {
        $this->put('APP_VERSION', $version);

        return $this;
    }

    /**
     * Update token .
     *
     * @param $token
     * @return $this
     */
    public function token($token) {
        $this->put('APP_TOKEN', $token);

        return $this;
    }

    /**
     * Update signed token .
     *
     * @param $signedToken
     * @return $this
     */
    public function sign($signedToken) {
        $this->put('APP_SIGNED', $signedToken);

        return $this;
    }

    /**
     * Enable maintenance mode
     */
    public function enableMaintenance() {
        $this->put('APP_MAINTENANCE', 'true');

        return $this;
    }

    /**
     * Disable maintenance mode
     *
     */
    public function disableMaintenance() {
        $this->put('APP_MAINTENANCE', 'false');

        return $this;
    }

    /**
     * Enable jobs
     *
     */
    public function enableJobs() {
        $this->put('APP_JOBS_ENABLED', 'true');

        return $this;
    }

    /**
     * Disable jobs
     *
     */
    public function disableJobs() {
        $this->put('APP_JOBS_ENABLED', 'false');

        return $this;
    }

}