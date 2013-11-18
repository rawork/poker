<?php

namespace Fuga\AdminBundle\Action;

class AddAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}

	/* Форма добавления */
	function getForm() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($this->get('util')->post('utype')) {
				if ($lastId = $this->dataTable->insertGlobals()) {
					$path = $this->fullRef.'/edit/'.$lastId;
					$_SESSION['message'] = 'Добавлено';
				} else {
					$path = $this->fullRef.'/add';
					$_SESSION['message'] = 'Ошибка добавления';
				}
				$this->get('router')->redirect($path);
			} else {
				$this->messageAction($this->dataTable->insertGlobals() ? 'Добавлено' : 'Ошибка добавления');
			}
		}
		if ($text = $this->render('admin/module/'.$this->get('router')->getParam('module').'.'.$this->get('router')->getParam('table').'.tpl', array(), true)){
			return $text;
		} else {
			$content = '';
			$fields = '';
			foreach ($this->dataTable->fields as $field) {
				if (empty($field['readonly'])) {
					$vis = '';
					if ($this->dataTable->dbName() == 'table_field' && ($field['name'] == 'select_values' || $field['name'] == 'params')) {
						$vis = ' style="display:none;"';
					}
					$fields .= '<tr'.$vis.' id="add_'.$field['name'].'"><td style="width:180px"><strong>'.$field['title'].'</strong>'.$this->getHelpLink($field).$this->getTemplateName($field).'</td><td>';
					$ft = $this->dataTable->getFieldType($field);
					$fields .= $ft->getInput().'</td></tr>';
				}
			}
			$content .= '<br><form enctype="multipart/form-data" method="post" id="entityForm" action="'.$this->fullRef.'/add">';
			$content .= '<input type="hidden" name="utype" value="0">';
			$content .= '<table class="table table-condensed">';
			$content .= '<thead><tr>';
			$content .= '<th>Новый элемент</th>';
			$content .= '<th></th></tr></thead>';
			$content .= $fields;
			$content .= '</table>
<input type="button" class="btn btn-success" onClick="preSubmit(0)" value="Сохранить">
<input type="button" class="btn btn-default" onClick="preSubmit(1)" value="Применить">
<a class="btn btn-danger" href="'.$this->fullRef.'">Отменить</a></form>';
			return $content;
		}
	}

	function getText() {
		$content = '';
		$links = array(
			array(
				'ref' => $this->fullRef,
				'name' => 'Список элементов'
			)
		);
		$content .= $this->getOperationsBar($links);
		$content .= $this->getForm();
		return $content;
	}

}
