<?php
/**
 * Numbat view handler
 */

class Views {
	/**
	 * Load a view
	 *
	 * @param Item $item Current Item being viewed
	 * @param array $request Request data from Controller
	 */
	public static function load($item, $request) {
		$name = 'View_' . $item->get_view();
		if(class_exists($name))
			$view = new $name();
		else
			$view = new View_Default();

		$view->setData($item->export(), $request);
		$view->render();
	}
}