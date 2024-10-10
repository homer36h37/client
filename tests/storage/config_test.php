<?php

date_default_timezone_set('America/Los_Angeles');

return array(

	/** Enable / Disable debug mode . */
	'debug' => true,

	/** That option will allow after update send debug.log file to server . */
	'allow_report' => true,

	/** Backup configurations . */
	'db' => array(
		/** That flag will backup current database with data before updating, in case of error database will be restored */
		'backup' => true,

		/** Backup path for database  */
		'backup_db_path' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test_storage/temp_dir/db/',
	),

	/** Register validators . */
	'validators' => array(
		\Client\Validator\Providers\DbConnection::class => 'db',
		\Client\Validator\Providers\PhpExtChecker::class => 'php5-ext',
		\Client\Validator\Providers\PhpChecker::class => 'php'
	),

	/** Backup path for files before updating, in case of error all these files will be restored */
	'backup_path' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test_storage/temp_dir/backup/',

	/** Temporary path for downloaded patch from server, will be serve as temporary directory where files will be unarchived */
	'temp_dir'    => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test_storage/temp_dir/',

	/** Directory for log file, will be served as log file for updating errors . */
	'log_file' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test_storage/log/debug_' . date('Y-m-d', time()) . '.log',

	/** Change log format %d:current_date, %v:current_version  */
	'log_format' => '%d '
);