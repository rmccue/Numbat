<?php
/**
 * HTML datatype
 *
 * @todo Visual editor?
 */

class Data_HTML extends Data_Text {
	public function __construct($name, $data) {
		parent::__construct($name, $data);
	}

	public function render_form() {
?>
					<label for="<?php echo $this->name ?>"><?php echo $this->name ?></label>
					<textarea class="data-largetext monospace" name="<?php echo $this->name ?>" id="<?php echo $this->name ?>"><?php echo htmlspecialchars($this->data['value']) ?></textarea>
<?php
	}
}