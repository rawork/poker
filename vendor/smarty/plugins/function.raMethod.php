<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raMethod} function plugin
 *
 * Type:     function<br>
 * Name:     raMethod<br>
 * Purpose:  initialize overlib
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_raMethod($params, &$smarty) {
	if (!isset($params['path'])) {
		$smarty->trigger_error('raMethod: Не указан параметр: path');
	} else {
		$args = array();
		$params['args'] = str_replace("'", '"', $params['args']);
		if (isset($params['args'])) {
			$args = json_decode(strtr($params['args'], '[]', '{}'), true);
			if (is_array($args)) {
				$args = array_values($args);
			} else {
				$args = array();
			}
		}
		return $GLOBALS['container']->callAction($params['path'], $args);
	}
}

?>
