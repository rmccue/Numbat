<?php
/**
 * View datatype
 *
 * Internal datatype for Numbat use only
 */

class Data_View extends Data_Text {
	public function __construct($name, $data) {
		parent::__construct($name, $data);
	}

	public function render_form() {
?>
				<label for="<?php echo $this->name ?>"><?php echo $this->name ?></label>
				<select class="data-select" name="<?php echo $this->name ?>" id="<?php echo $this->name ?>">
<?php
	foreach($this->get_all_views() as $view) {
		$current = ($view == $this->data['value']) ? ' selected' : '';
?>
					<option<?php echo $current ?>><?php echo $view ?></option>
<?php
	}
?>
				</select
<?php
	}

	protected function get_all_views() {
		$views = array();
		$app = glob(NUMBAT_APPPATH . '/views/*.php');
		$numbat = glob(NUMBAT_PATH . '/views/*.php');
		$merged = array_merge($app, $numbat);
		foreach($merged as $view) {
			$view = str_replace(NUMBAT_APPPATH . '/views/', '', $view);
			$view = str_replace(NUMBAT_PATH . '/views/', '', $view);
			$view = str_replace('.php', '', $view);
			$views[$view] = $view;
		}
		// Hide this, as it's for internal use only
		unset($views['admin']);
		return $views;
	}
}