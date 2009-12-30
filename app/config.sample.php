<?php
/**
 * Sample configuration for Numbat
 */

// Configuration
$config = array(
	// Database configuration
	'db' => array(
		'dsn' => 'mysql:dbname=numbat;host=127.0.0.1', // Database source name
		'user' => 'root', // Database username
		'pass' => 'password', // Database password
	),
	'baseurl' => 'http://localhost/numbat', // URL to access Numbat, without trailing slash
	'password' => 'somepass' // Password to gain access to administration
);

// Disable this when sending to production
define('NUMBAT_SHOW_DB_ERRORS', true);

// Sets the configuration
Config::instance()->set($config);