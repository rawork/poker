<?php

namespace Fuga\AdminBundle\Action;

	class SettingAction extends Action {
        function __construct(&$adminController) {
            parent::__construct($adminController);
        }
        function getText() {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$state = false;
				$params = $this->getManager('Fuga:Common:Param')->findAll($this->uai->module->name);
				foreach ($params as $param) {
					if ($this->get('util')->post('param_'.$param['name']) && $value = $this->validParam($this->get('util')->post('param_'.$param['name']), $param)) {
						$this->get('connection')->update('config_param',
							array('value' => $value), 
							array('name' => $param['name'], 'module' => $param['module'])
						);
						$state = true;
					} elseif ($param['type'] == 'bol') {
						$this->get('connection')->update('config_param', 
							array('value' => '0'),
							array('name' => $param['name'], 'module' => $param['module'])
						);		
						$state = true;
					} 
				}
				$this->uai->messageAction($state ? 'Настройки изменены' : 'Ошибки при изменении', $this->uai->getBaseTableRef().'/setting');
			}
			
			$ret = '';
			$ret .= '<form method="post" action="'.$this->fullRef.'/setting">';
			$ret .= '<table class="table table-condensed">';
			$ret .= '<thead><tr>';
			$ret .= '<th nowrap><b>Настройки модуля</b></th>';
			$ret .= '<th></th></tr></thead>';
			
			$params = $this->getManager('Fuga:Common:Param')->findAll($this->uai->module->name);
			foreach ($params as $param) {
				$ret .= '<tr><td align=left width="250"><strong>'.$param["title"].'</strong><br>{'.$param["name"].'}</td><td>';
				if ($param['type'] == 'bol') {
					if (intval($param['value']) > 0) {
						$text = 'checked';
					} else {
						$text = '';
					}
					$ret .= '<input type="checkbox" name="param_'.$param["name"].'" value="1" '.$text.'>';
				} elseif ($param['type'] == 'txt') {
					$ret .= '<textarea class="form-control" rows="5" name="param_'.$param["name"].'">'.$param["value"].'</textarea>';
				} else {
					$ret .= '<input class="form-control" type="text" name="param_'.$param["name"].'" value="'.$param["value"].'">';
				}
				$ret .= '</td></tr>';
			} 
            $ret .= '</table><br><input class="btn btn-success" type="submit" value="Сохранить изменения"> <a class="btn btn-default" href="'.$this->fullRef.'/setting">Отменить</a></form>';
			return $ret;
        }
		
		function validParam($value, $param = array()) {
			$ret = null;
			if ($param['type'] == 'bool') {
				if (intval($value) >= intval($param['minvalue']) && intval($value) <= intval($param['maxvalue'])) {
					$ret = intval($value);
				} else {
					$ret = intval($param['defaultvalue']);
				}
			} elseif ($param['type'] == 'int') {
				if (intval($value) >= intval($param['minvalue']) && intval($value) <= intval($param['maxvalue'])) {
					$ret = intval($value);
				} else {
					$ret = intval($param['defaultvalue']);
				}
			} else {
				$ret = $value;
			}
			return $ret;
		}
    }
?>