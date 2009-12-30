<?php
/**
 * Numbat database abstraction
 *
 * Partially inspirated by Anti-Framework's database class
 */

class Database {
	protected static $instance = null;
	protected static $count = 0;

	/**
	 * Constructor
	 *
	 * @param array $config Configuration, {@see Config}
	 * @throws NumbatDBError
	 */
	public function __construct($config) {
		if($config === null || empty($config['dsn']) || empty($config['user']))
			throw new NumbatDBError('Incomplete database configuration specified.', 0, $config);
		
		try {
			$this->db = new PDO($config['dsn'], $config['user'], $config['pass']);
		}
		catch ( PDOException $e ) {
			// We need the config for error display
			throw new NumbatDBError($e->getMessage(), $e->getCode(), $config);
		}
	}

	/**
	 * Singleton contoller
	 *
	 * @param array|null $config Configuration to pass to the constructor if needed
	 * @return Database Singleton instance of Database
	 */
	public static function &instance($config = null) {
		if ( empty(Database::$instance) ) {
			Database::$instance = new Database($config);
		}
		return Database::$instance;
	}

	/**
	 * Run a SELECT query
	 *
	 * @param string $condition Anything that would go in a WHERE clause
	 * @param array $parameters Must contain `table`. May contain `group`, `order`, `offset`, `limit`. All other parameters are used when binding by PDO
	 * @return array|bool False on failure, array of rows on success
	 */
	public function get($condition = null, $parameters = array()) {
		$parameters = (array) $parameters; 
		$sql = 'SELECT * FROM ' . $parameters['table'];
		if( $condition !== null )
			$sql .= ' WHERE ' . $condition;
		if( isset($parameters['group']) )
			$sql .= ' GROUP BY ' . $parameters['group'];
		if( isset($parameters['order']) )
			$sql .= ' ORDER BY ' . $parameters['order'];
		if( isset($parameters['limit'], $parameters['offset']) )
			$sql .= ' LIMIT ' . $parameters['offset'] . ', ' . $parameters['limit'];
		else if( isset($parameters['limit']) )
			$sql .= ' LIMIT ' . $parameters['limit'];
		unset($parameters['table']);
		unset($parameters['order']);
		unset($parameters['limit']);
		unset($parameters['offset']);
		unset($parameters['group']);
		
		return $this->query( $sql, $parameters );
	}

	/**
	 * Insert a row into the database
	 *
	 * @param array $parameters Must contain `table`
	 * @param array $vars Associative array of names->values to insert into the database
	 * @return int|false False on failure, row count on success
	 */
	public function insert($parameters, $vars) {
		$sql  = 'INSERT INTO ' . $parameters['table'];
		$sql .= '(`' . implode('`, `', array_keys($vars)) . '`) ';
		$sql .= 'VALUES (:' . implode(', :', array_keys($vars)) . ')';
		
		unset($parameters['table']);
		return $this->query( $sql, $vars, 'count' );
	}

	/**
	 * Insert a row into the database
	 *
	 * @param string $condition Anything that would go in a WHERE clause.
	 * @param array $parameters Must contain `table`
	 * @param array $vars Associative array of names->values to insert into the database
	 * @return int|false False on failure, row count on success
	 */
	public function update($condition, $parameters, $vars) {
		$sql = 'UPDATE ' . $parameters['table'] . ' SET ';
		foreach ( array_keys($vars) as $v )
			$sql .= '`' . $v . '` = :' . $v . ', ';
		$sql = substr($sql, 0, -2) . ' WHERE ' . $condition . ' LIMIT 1';
		
		unset($parameters['table']);
		return $this->query( $sql, $vars, 'count' );
	}

	/**
	 * Delete a row from the database
	 *
	 * @param string $condition Anything that would go in a WHERE clause.
	 * @param array $parameters Must contain `table`
	 * @return int|false False on failure, row count on success
	 */
	public function delete($condition, $parameters) {
		$sql = 'DELETE FROM ' . $parameters['table'] . ' WHERE ' . $condition;
		if( isset($parameters['limit']) )
			$sql .= ' LIMIT ' . $parameters['limit'];
		
		unset($parameters['table']);
		unset($parameters['limit']);
		return $this->query( $sql, $parameters, 'count' );
	}

	/**
	 * Run a raw SQL query
	 *
	 * @param string $sql SQL query to be prepared by PDO
	 * @param array $parameters Parameters to bind through PDO
	 * @param string $return 'count' to return a row count, 'rows' to return an associative array
	 * @return bool|int|array False on failure, otherwise integer or array based on the $return parameter
	 */
	public function query( $sql, $parameters, $return = 'rows' ) {
		$stmt = $this->db->prepare($sql);
		$status = $stmt->execute( (array) $parameters );
		
		$this->count++;
		
		if( !$status )
			return false;
		
		if ($return == 'count')
			return $stmt->rowCount();
		
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Returns the query count
	 */
	public function get_count() {
		return $this->count;
	}
}

/**
 * Custom exception class to hold config
 *
 * Adds the ability to hold the config that was passed into Database, for debugging
 */
class NumbatDBError extends Exception {
	protected $config;
	public function __construct($message, $code = 0, $config) {
		parent::__construct($message, $code);
		$this->config = $config;
	}

	public function getConfig() {
		return $this->config;
	}
}