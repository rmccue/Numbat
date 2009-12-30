<?php
/**
 * Numbat item
 */

class Item {
	/**
	 * Data from SQL query
	 * @var array
	 */
	protected $data = array();

	/**
	 * ID of self
	 * @var string
	 */
	protected $id = null;

	/**
	 * Constructor
	 *
	 * @param string $id ID to use in SQL queries
	 */
	public function __construct($id) {
		$this->id = $id;

		if(empty($id))
			return;

		$data = Database::instance()->get('id = :id', array('id' => $id, 'table' => 'numbat_items'));

		if ( empty($data) )
			throw new Numbat404($id);

		foreach ($data as $row) {
			$this->data[ $row['name'] ] = array('value' => $row['value'], 'type' => $row['type']);
		}
	}

	/**
	 * Get the "view" property
	 *
	 * @return string View name
	 */
	public function get_view() {
		if ( !empty($this->data['view']['value']) )
			return $this->data['view']['value'];
		
		return 'Default';
	}

	/**
	 * Update row
	 *
	 * @param string $key Name of row
	 * @param string $value New value
	 * @param string $type Data type to save
	 * @throws Exception
	 */
	public function update($key, $value, $type, $id = null) {
		if (!empty($id))
			$this->id = $id;
		
		if (!isset($this->data[$key]))
			$result = Database::instance()->insert(
				array('table' => 'numbat_items'), // Params
				array('id' => $this->id, 'name' => $key, 'value' => $value, 'type' => $type)
			);
		else
			$result = Database::instance()->update(
				'id = :id and name = :name', // WHERE
				array('table' => 'numbat_items'), // Params
				array('id' => $this->id, 'name' => $key, 'value' => $value) // Variables
			);
		if ($result != 1)
			throw new Exception('Update failed!');

		$this->data[$key]['value'] = $value;
	}

	/**
	 * Delete a row
	 *
	 * @param string $key Key to delete
	 */
	public function delete($key) {
		$result = Database::instance()->delete('id = :id and name = :name', array('id' => $this->id, 'name' => $key, 'table' => 'numbat_items', 'limit' => 1));
		if ($result != 1)
			throw new Exception('Update failed!');
	}

	/**
	 * Export item data
	 *
	 * @return array
	 */
	public function export() {
		return $this->data;
	}
}