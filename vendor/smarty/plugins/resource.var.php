<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Файл:     resource.var.php
 * Тип:     resource
 * Имя:     var
 * Назначение:  Получает шаблон из переменной
 * -------------------------------------------------------------
 */
function smarty_resource_var_source($tpl_name, &$tpl_source, &$smarty){
    if (!empty($GLOBALS['tplvar_'.$tpl_name])) {
        $tpl_source = $GLOBALS['tplvar_'.$tpl_name];
        return true;
    } else {
        return false;
    }
}

function smarty_resource_var_timestamp($tpl_name, &$tpl_timestamp, &$smarty) {
    $tpl_timestamp = time()+10;
    return true;
}

function smarty_resource_var_secure($tpl_name, &$smarty){
    // предполагаем, что шаблоны безопасны
    return true;
}

function smarty_resource_var_trusted($tpl_name, &$smarty){
    // не используется для шаблонов
}
?> 