<?php
/**
 * Numbat configuration
 */

class Config {
	protected static $instance = null;
	protected $config;

	public function __construct() {
	}

	public static function &instance() {
		if ( empty(Config::$instance) ) {
			Config::$instance = new Config();
		}
		return Config::$instance;
	}

	public function set($config) {
		$defaults = array(
			'db' => array(
				'dsn' => '',
				'user' => '',
				'pass' => '',
			),
			'baseurl' => 'http://localhost',
			'password' => false
		);
		$this->config = $this->merge($defaults, $config);
	}

	/**
	 * Merges any number of arrays of any dimensions, the later overwriting 
	 * previous keys, unless the key is numeric, in which case, duplicated 
	 * values will not be added.
	 *
	 * @access public
	 *
	 * @param array $array,... Arrays to merge
	 * @return array Resulting array, once all have been merged
	*/
	public function merge() {
		// Holds all the arrays passed
		$params = &func_get_args();
		
		// First array is used as the base, everything else overwrites on it
		$return = array_shift ( $params );
		
		// Merge all arrays on the first array
		foreach ( $params as $array ) {
			foreach ( $array as $key => $value ) {
				// Numeric keyed values are added (unless already there)
				if (is_numeric ( $key ) && (! in_array ( $value, $return ))) {
					if (is_array ( $value )) {
						$return [] = $this->merge ( $return [$key], $value );
					} else {
						$return [] = $value;
					}
					
				// String keyed values are replaced
				} else {
					if (isset ( $return [$key] ) && is_array ( $value ) && is_array ( $return [$key] )) {
						$return [$key] = $this->merge ( $return[$key], $value );
					} else {
						$return [$key] = $value;
					}
				}
			}
		}
		
		return $return;
	}

	public function get($key) {
		if(!empty($this->config[$key]))
			return $this->config[$key];
		
		return null;
	}

	public function getAll() {
		return $this->config;
	}
}