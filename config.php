<?php

date_default_timezone_set('America/Los_Angeles');

return array(

	/** Enable / Disable debug mode . */
	'debug' => true,

	/** Backup configurations . */
	'db' => array(
		/** That flag will backup current database with data before updating, in case of error database will be restored */
		'backup' => true,

		/** Backup path for database  */
		'backup_db_path' => ROOT_PATH . DIRECTORY_SEPARATOR . 'storage/backup/',
	),

	/** Register validators . */
	'validators' => array(
		\Client\Validator\Providers\IsWriteable::class => 'write',
		\Client\Validator\Providers\DbConnection::class => 'db',
		\Client\Validator\Providers\PhpExtChecker::class => 'php5-ext',
		\Client\Validator\Providers\PhpChecker::class => 'php'
	),

	/** Backup path for files before updating, in case of error all these files will be restored */
	'backup_path' => ROOT_PATH . DIRECTORY_SEPARATOR . 'storage/temp_dir/backup/',

	/** Temporary path for downloaded patch from server, will be serve as temporary directory where files will be unarchived */
	'temp_dir'    => ROOT_PATH . DIRECTORY_SEPARATOR . 'storage/temp_dir/',

	/** Log path  */
	'log_path' => ROOT_PATH . DIRECTORY_SEPARATOR . 'storage/log/'
);