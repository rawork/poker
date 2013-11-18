<?php

namespace Fuga\AdminBundle\Action;
	
class EditAction extends Action {

	public $item;

	function __construct(&$adminController) {
		parent::__construct($adminController);
		$this->item = $this->dataTable->getItem($this->get('router')->getParam('id')); 
	}

	function getForm() {
		if ($this->get('util')->post('id')) {
			if ($this->get('util')->post('utype')) {
				$_SESSION['message'] = ($this->dataTable->updateGlobals() ? 'Обновлено' : 'Ошибка обновления');
				$this->get('router')->redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->messageAction($this->dataTable->updateGlobals() ? 'Обновлено' : 'Ошибка обновления');
			}
		}
		$ret = '';
		if (!$this->item) {
			$this->get('router')->redirect($this->fullRef);
		}
		if ($this->item) {
			$svalues = explode(';', 'Строка|string;Текст|text;Булево|checkbox;Файл|file;Выбор|select');
			foreach ($svalues as $valueItem) {
				$types[] = explode('|', $valueItem);
			}
			$params = array(
				'entity' => $this->item,
				'types' => $types
			);
			$template = 'admin/module/'.$this->get('router')->getParam('module').'.'.$this->get('router')->getParam('table').'.tpl';
			if ($text = $this->render($template, $params, true)) {
				return $ret.$text;
			} else {
				$ret .= '<form enctype="multipart/form-data" method="post" id="entityForm" action="'.$this->fullRef.'/edit">';
				$ret .= '<input type="hidden" name="id" value="'.$this->item['id'].'">';
				$ret .= '<input type="hidden" id="utype" name="utype" value="0">';
				$ret .= '<table class="table table-condensed">';
				$ret .= '<thead><tr>';
				$ret .= '<th>Редактирование</th>';
				$ret .= '<th>Запись: '.$this->item['id'].'</th></tr></thead>';
				foreach ($this->dataTable->fields as $name => $field) {
					$ft = $this->dataTable->getFieldType($field, $this->item);
					$ret .= '<tr><td align="left" width=150><strong>'.$field['title'].'</strong>'.$this->getHelpLink($field).$this->getTemplateName($field).'</td><td>';
					$ret .= !empty($field['readonly']) ? $ft->getStatic() : $ft->getInput();
					$ret .= '</td></tr>';
				}
				$ret .= '</table>
<input type="button" class="btn btn-success" onClick="preSubmit(0)" value="Сохранить">
<input type="button" class="btn btn-default" onClick="preSubmit(1)" value="Применить">
<input type="button" class="btn btn-danger" onClick="window.location = \''.$this->fullRef.'\'" value="Отменить"></form>';
			}
		}
		return $ret;
	}

	function getText() {
		$links = array(
			array(
				'ref' => $this->fullRef,
				'name' => 'Список элементов'
			)
		);
		$content = $this->getOperationsBar($links);
		$content .= $this->getForm();
		return $content;
	}
}

