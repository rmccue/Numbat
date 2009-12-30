<?php
/**
 * Numbat default view
 *
 * You should override this in your app/views folder
 */

class View_Default {
	protected $data = array();
	protected $request = array();

	public function setData($data, $request) {
		$this->data = $data;
		$this->request = $request;
	}

	public function render() {
?>
<!doctype html>
<html>
<head>
	<title>Numbat - <?php $this->output('title') ?></title>
	<link rel="stylesheet" href="<?php echo $this->config()->get('baseurl') ?>/numbat/static/style.css" />
</head>
<body>
	<div class="container">
		<?php $this->output('content') ?>
		<div id="footer"><p><?php echo numbat_session_stats() ?>. Powered by <a href="http://ryanmccue.info/projects/numbat">Numbat</a>.</p></div>
	</div>
</body>
</html>
<?php
	}

	protected function &config() {
		return Config::instance();
	}

	protected function parse($value) {
		$value = preg_replace_callback('/{([^}]+)}/', array(&$this, 'parse_callback'), $value);
		return $value;
	}

	protected function parse_callback($matches) {
		$full = $matches[1];
		
		$vars = explode('.', $full, 2);
		$type = $vars[0];
		
		if(!empty($vars[1]))
			$name = $vars[1];
		else
			$name = $full;
		
		switch ( $type ) {
			case 'config':
				if ( $this->config()->get($name) !== null )
					return $this->config()->get($name);
				break;
			case 'req':
				if ( !empty( $this->request[$name] ) )
					return $this->request[$name];
			default:
				if ( $this->get($full) !== null )
					return $this->get($full);
		}
		
		return $matches[0];
	}

	protected function get($name, $parse = false) {
		if ($parse) {
			if ( empty( $this->data[$name]['parsed']) )
				$this->data[$name]['parsed'] = $this->parse($this->data[$name]['value']);
			return $this->data[$name]['parsed'];
		}
		
		if (!empty($this->data[$name]['value']))
			return $this->data[$name]['value'];
		else
			return null;
	}

	protected function output($name, $parse = false) {
		echo $this->get($name, $parse);
	}

	protected function getType($name) {
		return $this->data[$name]['type'];
	}
}