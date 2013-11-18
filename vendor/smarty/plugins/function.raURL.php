<?php
/**
 * Smarty plugin
 * @package Web2b
 * @subpackage plugins
 */

/**
 * Smarty {raURL} function plugin
 *
 * Type:     function<br>
 * Name:     raURL<br>
 * Purpose:  initialize overlib
 * @author   Roman Alyakrytskiy
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_raURL($params, &$smarty) {
	$ret = '';
	$separator = !empty($params['spt']) ? $params['spt'] : '\*';
    $method = isset($params['method']) ? $params['method'] : 'index';
    $prms = !empty($params['prms']) ? explode('.', $params['prms']) : array();
    $link = !empty($params['node']) ? (stristr($params['node'], 'http://') ? $params['node'] : $GLOBALS['container']->get('router')->generateUrl($params['node'], $method , $prms)) : '';
    if (!empty($params['text'])) {
        if (empty($params['node']) && empty($params['url'])) {
        	return str_replace(str_replace('\\', '', $separator), '', $params['text']);
        } else {    
            $ret = trim($params['text']);
    		if ($ret && preg_match_all('/'.$separator.'([^\*]+)'.$separator.'/', $ret, $matches)){
            	foreach ($matches[0] as $m) {
            		$ret = str_replace($m, '<a href="'.$link.'">'.str_replace(str_replace('\\', '', $separator), '', $m).'</a>',$ret);
            	}
            }
            return $ret;
        }    
    } else {
        return $link;
    }
}

?>
