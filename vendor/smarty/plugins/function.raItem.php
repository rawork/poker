<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raItem} function plugin
 *
 * Type:     function<br>
 * Name:     raItem<br>
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return array
 */

function smarty_function_raItem($params, &$smarty) {
	if (!isset($params['table'])) {
		$smarty->trigger_error('raItem: Не указан параметр: table');
	} else {
		$class = $params['table'];
		$where = isset($params['query']) ? $params['query'] : 0;
		if (empty($where)){
			$smarty->trigger_error('raItem: Не указан параметр отбора');
		} elseif (!isset($params['var'])) {
			$smarty->trigger_error('raItem: Не указан параметр: var');
		} else {
			$smarty->assign($params['var'], $GLOBALS['container']->getItem($class, $where, !empty($params['sort']) ? $params['sort'] : '', !empty($params['select']) ? $params['select'] : ''));
		}
	}
}

?>
