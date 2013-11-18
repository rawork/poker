<?php
/**
 * Smarty plugin
 * @package Fuga
 * @subpackage plugins
 */

/**
 * Smarty t modifier plugin
 *
 * Type:     modifier<br>
 * Name:     t<br>
 * Purpose:  translate string
 * @param string
  * @return string
 */

function smarty_modifier_t($string) {
	return $GLOBALS['container']->get('translator')->t($string);
}


?>
