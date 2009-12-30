<?php
/**
 * Numbat bootstrapper
 */

$GLOBALS['numbat_start_time'] = microtime(true);

/**
 * Attempt to load the class before PHP fails with an error.
 * This method is called automatically in case you are trying to use a class which hasn't been defined yet.
 *
 * We look for the undefined class in the following folders:
 * - /system/classes/*.php
 * - /user/classes/*.php
 * - /user/sites/x.y.z/classes/*.php
 *
 * @param string $class_name Class called by the user
 */
function numbat_autoload($class_name) {
	$class_file = strtolower($class_name) . '.php';

	$type = explode('_', strtolower($class_name), 2);
	$type = $type[0];

	switch ($type) {
		case 'view':
			$dirs = array( NUMBAT_APPPATH . '/views', NUMBAT_PATH . '/views' );
			$class_file = str_replace('view_', '', $class_file);
			break;
		case 'data':
			$dirs = array( NUMBAT_APPPATH . '/datatypes', NUMBAT_PATH . '/datatypes' );
			$class_file = str_replace('data_', '', $class_file);
			break;
		default:
			$dirs = array( NUMBAT_APPPATH . '/classes', NUMBAT_PATH . '/classes');
	}

	foreach ($dirs as $dir) {
		if(file_exists($dir . '/' . $class_file)) {
			require_once($dir . '/' . $class_file);
			break;
		}
	}
}
spl_autoload_register('numbat_autoload');
set_exception_handler(array('Controller', 'handle_exception'));

/*
 * Navigates through an array and removes slashes from the values.
 *
 * If an array is passed, the array_map() function causes a callback to pass the
 * value back to the function. The slashes from this value will removed.
 *
 * @author WordPress
 *
 * @param array|string $value The array or string to be striped.
 * @return array|string Stripped array (or string in the callback).
 */
function stripslashes_deep($value) {
	 $value = is_array($value) ?
		 array_map('stripslashes_deep', $value) :
		 stripslashes($value);

	 return $value;
}

/**
 * Error notice if we haven't yet loaded the database
 *
 * If the database has been loaded, the error page will be shown instead of
 * this.
 *
 * @param string $message Human readable error message
 * @param string $type Type of error, 'config' for configuration error, 'db' for database or 'uncaught' for uncaught exceptions
 * @config array $config Database configuration for debugging purposes. Use with $type = 'db' only
 */
function numbat_primative_die($message, $type = 'config', $config = array()) {
?>
<!doctype html>
<html>
<head>
	<title>Numbat - Error</title>
	<!-- The following works regardless of access URL -->
	<style><?php include(NUMBAT_PATH . '/static/style.css') ?></style>
</head>
<body>
	<div class="container error">
		<h1>Error</h1>
		<p>Numbat encountered a serious error while loading, and could not continue.</p>
		<dl>
			<dt>Type</dt>
			<dd>
<?php
	switch ( $type ) {
		case 'db':
			echo 'Database error';
			break;
		case 'config':
			echo 'Configuration';
			break;
		case 'uncaught':
		default:
			echo 'Uncaught exception';
			break;
	}
?></dd>
<?php
	if($type != 'db') {
?>
			<dt>Message</dt>
			<dd class="error <?php echo $type ?>"><?php echo $message; ?></dd>
<?php
	}
	elseif ( defined('NUMBAT_SHOW_DB_ERRORS') && NUMBAT_SHOW_DB_ERRORS) {
?>
			<dt>Message</dt>
			<dd class="error db"><?php echo $message ?></dd>
<?php
		if(!empty($config)) {
			$config['pass'] = '*** PASSWORD ***';
?>
			<dt>Supplied configuration</dt>
			<dd><pre><?php var_dump($config) ?></pre></dd>
<?php
		}
	}
?>
		</dl>
	</div>
</body>
</html>
<?php

	die();
}

function numbat_session_stats() {
	$time = microtime(true) - $GLOBALS['numbat_start_time'];
	return 'Rendered in ' . round($time, 3) . ' seconds, with ' . Database::instance()->get_count() . ' queries';
}

/** Load app configuration if it exists */
if(file_exists(NUMBAT_APPPATH . '/config.php'))
	include(NUMBAT_APPPATH . '/config.php');
else
	numbat_primative_die("Couldn't load configuration", 'config');

/** Make sure constant is defined */
if ( !defined('NUMBAT_SHOW_DB_ERRORS') )
	define('NUMBAT_SHOW_DB_ERRORS', false);

/** Undo magic quotes **/
if (get_magic_quotes_gpc()) {
	list($_GET, $_POST, $_COOKIE, $_REQUEST) = stripslashes_deep(array($_GET, $_POST, $_COOKIE, $_REQUEST));
}

// Connect to database
Database::instance(Config::instance()->get('db'));
Controller::parse();
Controller::handle();