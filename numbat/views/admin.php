<?php
/**
 * Numbat administration view
 */

class View_Admin {
	protected $data = array();
	protected $request = array();

	public function setData($data, $request) {
		$this->data = $data;
		$this->request = $request;
	}

	public function render() {
		$parts = explode('/', $this->request['page']);

		if($parts[0] !== 'admin')
			throw new Exception('');

		if ( empty($parts[1]) )
			$parts[1] = 'default';

		switch ( $parts[1] ) {
			case 'api':
				$this->api();
				break;
			case 'single':
				$this->render_single();
				break;
			case 'add':
				$this->add();
				break;
			default:
				$this->render_list();
				break;
		}
	}

	protected function api() {
		header('Content-Type: application/json');
		try {
			if (empty($_REQUEST['method']))
				throw new Exception('No method specified');
			
			switch ($_REQUEST['method']) {
				case 'add-row':
					$this->add_row();
					break;
				case 'delete-row':
					$this->delete_row();
					break;
				case 'delete-item':
					if (empty($_REQUEST['item']))
						throw new Exception('No item specified');
					$item = new Item($_REQUEST['item']);
					$item->delete_item();
					die(json_encode(array('msg' => 'Deleted item', 'code' => 0)));
					return;
				default:
					throw new Exception('Invalid method specified');
			}
		}
		catch (Exception $e) {
			header('HTTP/1.0 500 Internal Server Error');
			$error = array(
				'msg' => $e->getMessage(),
				'code' => $e->getCode(),
				'error' => true,
			);
			echo json_encode($error);
			die();
		}
	}

	protected function add_row() {
		if (empty($_POST['item']))
			throw new Exception('No item specified');
		
		if (empty($_POST['name']))
			throw new Exception('No field name specified');
		
		if (empty($_POST['type']))
			throw new Exception('No datatype specified');
		
		$item = new Item($_POST['item']);
		$item->update($_POST['name'], '', $_POST['type']);
		
		$type = 'Data_' . $_POST['type'];
		if (!class_exists($type))
			$type = 'Data_Text';
		$type = new $type($_POST['name'], array('type' => $_POST['type'], 'value' => ''));
		
		ob_start();
		$type->render_form();
		$row = ob_get_clean();
		
		echo json_encode(array(
			'msg' => 'Added row',
			'code' => 0,
			'row' => $row
		));
		die();
	}

	protected function delete_row() {
		if (empty($_REQUEST['item']))
			throw new Exception('No item specified');
		
		if (empty($_REQUEST['name']))
			throw new Exception('No field name specified');
		
		$item = new Item($_REQUEST['item']);
		$item->delete($_REQUEST['name']);
		
		echo json_encode(array(
			'msg' => 'Removed row',
			'code' => 0
		));
		die();
	}

	protected function header() {
?><!doctype html>
<html>
<head>
	<title>Numbat - <?php echo $this->title ?></title>
	<link rel="stylesheet" href="<?php echo Config::instance()->get('baseurl') ?>/numbat/static/style.css" />
	<link rel="stylesheet" href="<?php echo Config::instance()->get('baseurl') ?>/numbat/static/admin.css" />
</head>
<body>
	<div class="container">
		<p id="navigation">
			<a href="<?php echo Config::instance()->get('baseurl') ?>/">Back to site</a> &mdash;
			<a href="<?php echo Config::instance()->get('baseurl') ?>/admin/">Admin Home</a> &mdash;
			<a href="<?php echo Config::instance()->get('baseurl') ?>/admin/add">Add Item</a>
		</p>
<?php
	}

	protected function footer() {
?>
		<div id="footer"><p><?php echo numbat_session_stats() ?>. Powered by <a href="http://ryanmccue.info/projects/numbat">Numbat</a>.</p></div>
	</div>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
	<script src="<?php echo Config::instance()->get('baseurl') ?>/numbat/static/admin.js"></script>
</body>
</html>
<?php
	}

	protected function render_list() {
		$this->title = 'Items';
		$this->header();
?>
		<h1>Available Items</h1>
		<table>
			<tbody>
<?php
		$exclude = array('admin', 'admin/api', 'admin/single');
		$items = Database::instance()->get(null, array('table' => 'numbat_items', 'group' => '`id`', 'limit' => 25));
		foreach ($items as $item) {
			if (in_array($item['id'], $exclude))
				continue;
?>
				<tr>
					<th scope="row"><?php echo $item['id'] ?></td>
					<td><a href="<?php echo Config::instance()->get('baseurl') ?>/admin/single?id=<?php echo $item['id'] ?>">Change</a></td>
					<!--<td><a href="<?php echo Config::instance()->get('baseurl') ?>/admin/single?id=<?php echo $item['id'] ?>">Remove</a></td>-->
				</tr>
<?php
		}
?>
			</tbody>
		</table>
<?php
		$this->footer();
	}

	protected function add() {
		$success = true;
		if (!empty($_POST['submit']))
			$success = $this->add_item();
		
		$this->title = 'Add Item';
		$this->header();
?>
		<h1>Add Item</h1>
<?php
		if (!$success) {
?>
		<p class="notice">Could not add item.</p>
<?php
		}
?>
		<form method="POST">
			<div class="form-row">
				<label for="id">id</label>
				<input type="text" id="id" name="id" value="" />
				<p>This is the URL part, such as <code>some/page</code>. You cannot change this later.</p>
			</div>
			<div class="form-row">
<?php
		$type = new Data_View('view', array('type' => 'View', 'value' => ''));
		$type->render_form();
?>
			</div>
			<div class="submit-row">
				<input type="submit" name="submit" class="default" value="Submit" />
				<input type="reset" value="Reset" />
			</div>
		</form>
<?php
		$this->footer();
	}

	protected function add_item() {
		if (empty($_POST['id']) || empty($_POST['view']))
			return false;
		
		$item = new Item(null);
		$item->update('view', $_POST['view'], 'View', $_POST['id']);
		header('Location: ' . Config::instance()->get('baseurl') . '/admin/single?id=' . $_POST['id']);
		die();
	}

	protected function render_single() {
		$item = new Item($_GET['id']);
		
		$changed = (empty($_GET['changed'])) ? false : true;
		if(!empty($_POST['submit']))
			$this->change_single($item);
		
		$this->title = 'Change Item';
		$this->header();
?>
		<h1>Change item</h1>
<?php
		if ($changed) {
?>
		<p class="notice">Updated item.</p>
<?php
		}
?>
		<form method="POST">
			<fieldset class="main">
				<input type="hidden" name="id" id="id" value="<?php echo $_GET['id'] ?>" />
<?php
		foreach ($item->export() as $name => $data) {
			$type = 'Data_' . $data['type'];
			if (!class_exists($type))
				$type = 'Data_Text';
			$data = new $type($name, $data);
			
			$class = '';
			if($type == 'Data_View')
				$class = ' no-delete';
?>
				<div class="form-row<?php echo $class ?>">
<?php
			$data->render_form()
?>
				</div>
<?php
		}
?>
			</fieldset>
			<fieldset class="add">
				<h2>Add Row</h2>
				<div class="form-row">
					<label for="add-name">Name</label>
					<input type="text" id="add-name" name="add-name" value="" />
				</div>
				<div class="form-row">
					<label for="add-type">Type</label>
					<select name="add-type" id="add-type">
<?php
		foreach ($this->get_all_types() as $type) {
?>
						<option><?php echo $type ?></option>
<?php
		}
?>
					</select>
				</div>
				<button>Add</button>
			</fieldset>
			<div class="submit-row">
				<input type="submit" name="submit" class="default" value="Submit" />
				<p class="deletelink-box"><a href="#delete" class="deletelink">Delete</a></p>
				<button id="add-button">Add Row</button>
				<input type="reset" value="Reset" />
			</div>
		</form>
<?php
		$this->footer();
	}

	protected function change_single(&$item) {
		$changed = false;
		foreach ($item->export() as $name => $data) {
			$type = 'Data_' . $data['type'];
			if (!class_exists($type))
				$type = 'Data_Text';
			$type = new $type($name, $data);
			
			if(!empty($_POST[$name]) && $_POST[$name] != $data['value']) {
				$item->update($name, $type->convert($_POST[$name]), $data['type']);
				$changed = true;
			}
		}
		
		if ($changed)
			header('Location: ' . Config::instance()->get('baseurl') . '/admin/single?changed=1&id=' . $_GET['id']);
		else
			header('Location: ' . Config::instance()->get('baseurl') . '/admin/single?id=' . $_GET['id']);
		
		die();
	}

	protected function get_all_types() {
		$types = array();
		$app = glob(NUMBAT_APPPATH . '/datatypes/*.php');
		$numbat = glob(NUMBAT_PATH . '/datatypes/*.php');
		$merged = array_merge($app, $numbat);
		foreach($merged as $view) {
			$view = str_replace(NUMBAT_APPPATH . '/datatypes/', '', $view);
			$view = str_replace(NUMBAT_PATH . '/datatypes/', '', $view);
			$view = str_replace('.php', '', $view);
			$types[$view] = $view;
		}
		// Hide this, as it's for internal use only
		unset($types['view']);
		return $types;
	}
}