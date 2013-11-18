<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */


/**
 * Smarty string_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     string_format<br>
 * Purpose:  format strings via sprintf
 * @link http://smarty.php.net/manual/en/language.modifier.string.format.php
 *          string_format (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param string
 * @return string
 */
function smarty_modifier_number_format($string, $decimals = 0, $dec_point = '', $thousands_sep = '')
{
    return number_format($string, $decimals, $dec_point, $thousands_sep);
}

/* vim: set expandtab: */

?>
