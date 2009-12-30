<?php
/**
 * Numbat controller
 */

class Controller {
	/**
	 * Request name
	 * @var string
	 */
	protected static $request = '';

	/**
	 * Parse the HTTP request
	 */
	public static function parse() {
		/* Grab the base URL from the Site class */
		$baseurl = parse_url( Config::instance()->get('baseurl') );
		$baseurl = $baseurl['path'];

		/* Start with the entire URL coming from web server... */
		if(isset($_SERVER['REQUEST_URI']))
			$request = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		else
			$request = $_SERVER['SCRIPT_NAME'] . ( isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '' );

		/* Strip out the base URL from the requested URL */
		/* but only if the base URL isn't / */
		if ( '/' != $baseurl ) {
			$request = str_replace($baseurl, '', $request);
		}

		$request = trim($request, '/');
		self::$request = $request;
	}

	/**
	 * Handle the request
	 */
	public static function handle() {
		if(empty(self::$request))
			self::$request = 'default';
		
		$req = array(
			'page' => self::$request,
			'url' => Config::instance()->get('baseurl') . '/' . self::$request,
			'time' => time(),
			'error' => false
		);
		
		try {
			$item = new Item(self::$request);
			Views::load($item, $req);
		}
		catch (NumbatDBError $e) {
			numbat_primative_die($e->getMessage(), 'db', $e->getConfig());
		}
		catch (Numbat404 $e) {
			$req['error'] = true;
			$item = new Item('error/404');
			Views::load($item, $req);
		}
	}

	/**
	 * Generic exception handler
	 *
	 * Handles uncaught exceptions, such as database errors
	 * @param Exception $e Any type of exception
	 */
	public static function handle_exception($e) {
		switch ( get_class($e) ) {
			case 'NumbatDBError':
			case 'PDOException':
				$type = 'db';
				break;
			default:
				$type = 'uncaught';
				break;
		}

		$config = array();
		if($type == 'db' && get_class($e) == 'NumbatDBError')
			$config = $e->getConfig();

		numbat_primative_die($e->getMessage(), $type, $config);
	}
}

/**
 * 404 error
 */
class Numbat404 extends Exception {
	public function __construct($id) {
		parent::__construct('Not found: ' . $id, 404);
	}
}