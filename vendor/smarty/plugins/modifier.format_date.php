<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raDate} function plugin
 *
 * Type:     function<br>
 * Name:     raDate<br>
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return array
 */
/**
 * Smarty string_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     fdate<br>
 * Purpose:  format date
 * @link http://smarty.php.net/manual/en/language.modifier.string.format.php
 *          string_format (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param string
 * @return string
 */

function smarty_modifier_format_date($string, $format = 'd.m.Y') {
	return $GLOBALS['container']->get('util')->format_date($string, $format);
}


?>
