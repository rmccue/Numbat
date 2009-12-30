<?php
/**
 * Standard text datatype
 */

class Data_Text {
	protected $name = '';
	protected $data = '';

	public function __construct($name, $data) {
		$this->name = htmlspecialchars($name);
		$this->data = $data;
	}

	public function render_form() {
?>
					<label for="<?php echo $this->name ?>"><?php echo $this->name ?></label>
					<input type="text" class="data-text" name="<?php echo $this->name ?>" id="<?php echo $this->name ?>" value="<?php echo htmlspecialchars($this->data['value']) ?>" />
<?php
	}

	public function convert($new_data) {
		return $new_data;
	}
}