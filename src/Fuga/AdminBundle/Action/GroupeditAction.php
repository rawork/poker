<?php

namespace Fuga\AdminBundle\Action;

class GroupeditAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	
	function getText() {
		$ids = $this->get('util')->post('ids');
		if (!$ids || $this->get('util')->post('edited', true, 0)) {
			$this->messageAction($this->dataTable->group_update() ? 'Обновлено' : 'Ошибка обновления записей');
		}
		$content = '';
		$this->dataTable->select(
			array (
				'where' => 'id IN('.$ids.')',
			)
		);
		while ($entity = $this->dataTable->getNextArray(false)) {
			$content .= '<tr>
<th>Редактирование</th>
<th>Запись: '.$entity['id'].'</th>
</tr>';
			foreach ($this->dataTable->fields as $field) {
				$ft = $this->dataTable->getFieldType($field, $entity);
				$content .= '<tr><td width="150"><strong>'.$field['title'].'</strong>'.$this->getHelpLink($field).$this->getTemplateName($field).'</td><td>';
				if (!empty($field['readonly'])) {
					$content .= $ft->getStatic();
				} else {
					$content .= $ft->getInput('', $ft->getName().$entity['id']);
				}
				$content .= '</td>
</tr>';
			}
		}
		if ($content) {
			$links = array(
				array(
					'ref' => $this->fullRef,
					'name' => 'Список элементов'
				)
			);
			$content = $this->getOperationsBar($links).'<table class="table table-condensed">'.$content;
			$content = '<form enctype="multipart/form-data" method="post" id="entityForm" action="'.$this->fullRef.'/groupedit">
<input type="hidden" name="edited" value="1">
<input type="hidden" name="ids" value="'.$ids.'">'.$content.'
</table>
<input type="button" class="btn btn-success" onClick="preSubmit(0)" value="Сохранить">
<input type="button" class="btn btn-default" onClick="window.location = \''.$this->fullRef.'\'" value="Отменить">				
</form>';
		}
		return $content;
	}

}
