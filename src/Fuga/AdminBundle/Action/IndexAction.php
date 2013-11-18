<?php
	
namespace Fuga\AdminBundle\Action;

class IndexAction extends Action {

	private $showGroupSubmit	= false;
	private $paginator			= null;
	private $elementsIds		= array();
	protected $rowPerPage		= 25;

	function __construct(&$adminController) {
		parent::__construct($adminController);
		$this->rowPerPage = $this->get('util')->session($this->dataTable->dbName().'_rpp', true, $this->rowPerPage);
		$this->paginator = $this->get('paginator');
	}	

	/* Кнопки управления записью */
	private function _getUpdateDelete($id) {
		$ref = explode('?', $this->fullRef);
		$buttons = '<td>
<div class="btn-group pull-right">
  <a class="btn btn-default btn-sm dropdown-toggle admin-dropdown-toggle" id="drop'.$id.'" data-toggle="dropdown" href="#">
    <span class="glyphicon glyphicon-align-justify"></span>
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu admin-dropdown-menu">
    <li><a href="'.$ref[0].'/edit/'.$id.'"><i class="glyphicon glyphicon-pencil"></i> Изменить</a></li>
    <li><a href="javascript: startDelete('.$id.')"><i class="glyphicon glyphicon-trash"></i> Удалить</a></li>
    <li><a href="javascript: showCopyDialog('.$id.')"><i class="glyphicon glyphicon-random"></i> Копировать</a></li>
  </ul>
</div>
</td>
';
		return $buttons;
	}
	
	private function showCredate() 
	{
		return !empty($this->dataTable->params['show_credate']);
	}

	private function getTableContent() {
		$tableHtml = '';
		$this->paginator->paginate(
			$this->dataTable, 
			$this->searchRef.'?page=###', 
			$this->search_sql, 
			$this->rowPerPage, 
			$this->get('util')->request('page', true, 1), 
			10
		);
		$this->dataTable->select(
			array (
				'where' => $this->search_sql,
				'limit' => $this->paginator->limit
			)
		);
		$entities = $this->dataTable->getNextArrays(false);
		foreach ($entities as $entity) {
			$this->elementsIds[] = $entity['id'];
			$tableHtml .= '<tr>';
			$tableHtml .= '
<td><input type="checkbox" class="list-checker" value="'.$entity['id'].'"></td>
<td>'.$entity['id'].'</td>';
			reset($this->dataTable->fields);
			foreach ($this->dataTable->fields as $field) {
				if (!empty($field['width'])) {
					$ft = $this->dataTable->getFieldType($field, $entity);
					$tableHtml .= '<td>';
					$sFieldHtml = '';
					if (!empty($field['group_update']) && empty($field['readonly'])) {
						$sFieldHtml .= $ft->getGroupInput();
						$this->showGroupSubmit = true;
					} else {
						$sFieldHtml .= $ft->getGroupStatic();
					}
					$tableHtml .= ($sFieldHtml ? $sFieldHtml : '&nbsp;').'</td>'."\n";
				}
			}
			if ( $this->dataTable->params['show_credate'] ) {
				$tableHtml .= '<td>'.$entity['created'].'</td>'."\n";
			}
			$tableHtml .= $this->_getUpdateDelete($entity['id']).'</tr>'."\n";
		}
		$params = array(
			'tableData' => $tableHtml,
			'baseRef' => $this->baseRef,
			'paginator' => $this->paginator,
			'showCredate' => $this->showCredate(),
			'fields' => $this->dataTable->fields,
			'rpps' => array(10,25,50,100,200),
			'rowPerPage' => $this->rowPerPage,
			'ids' => join(',', $this->elementsIds),
			'isView' => !empty($this->dataTable->params['is_view']),
			'tableName' => $this->dataTable->dbName(),
			'showGroupSubmit' => $this->showGroupSubmit
		);
		
		return $this->render('admin/action/action.index.tpl', $params);
	}

	private function getTree($parentId, $prefixWidth = 0, $styleClass = '') {
		$tableHtml = '';
		$where = 'parent_id='.$parentId.' '.($this->search_sql ? ' AND '.$this->search_sql : '');
		$this->dataTable->select(
			array(
				'where'		=> '1=1',
				'order_by'	=> 'left_key'
			)
		);
		$nodes = $this->dataTable->getNextArrays();
		$styleClass .= 't'.$parentId;
		foreach ($nodes as $node) {
			$this->elementsIds[] = $node['id'];
			$tableHtml .= '<tr rel="'.$node['parent_id'].'" class="'.$styleClass.'">';
			$tableHtml .= '<td width="1%"><input type="checkbox" class="list-checker" value="'.$node['id'].'"></td><td width="1%">'.$node['id'].'</td>';
			$num = 0;

			$prefixWidth = 20 * ((int)$node['level']-1);
			foreach ($this->dataTable->fields as $field) {
				if (!empty($field['width'])) {
					$tableHtml .= '<td width="'.$field['width'].'">';
					if ($num == 0) {
						$tableHtml .= '<span><div style="display:inline-block;width:'.$prefixWidth.'px"></div> &rarr; </span>';
					}
					$ft = $this->dataTable->getFieldType($field, $node);
					if (!empty($field['group_update']) && empty($field['readonly'])) {
						$tableHtml .= $ft->getGroupInput();
						$this->showGroupSubmit = true;
					} else {
						$tableHtml .= $ft->getStatic();
					}
					if ($num == 0) {
						if ($this->dataTable->dbName() == 'page_page' && isset($node['module_id_value']['item'])) {
							$module = $this->get('container')->getModule($node['module_id_value']['item']['name']);
							if ( $module ) {
								$tableHtml .= ' (тип &mdash; '.$module['title'].')';
							}
						}
					}
					$tableHtml .= '</td>';
				}
				$num++;
			}
			$tableHtml .= $this->_getUpdateDelete($node['id']).'</tr>';
		}
		
		return $tableHtml;
	}

	private function getTreeContent() {
		$params = array(
			'tableData' => $this->getTree(0, 0, ''),
			'baseRef' => $this->baseRef,
			'paginator' => $this->paginator,
			'showCredate' => $this->showCredate(),
			'fields' => $this->dataTable->fields,
			'ids' => join(',', $this->elementsIds),
			'isView' => !empty($this->dataTable->params['is_view']),
			'showGroupSubmit' => $this->showGroupSubmit
		);
		return $this->render('admin/action/action.index.tpl', $params);
	}

	private function _getFilterForm() {
		foreach ($this->dataTable->fields as &$field) {
			if (!empty($field['search'])) {
				$ft = $this->dataTable->getFieldType($field);
				$field['searchinput'] = $ft->getSearchInput();
			}
		}
		unset($field);
		$params = array(
			'baseRef' => $this->baseRef,
			'fields' => $this->dataTable->fields
		);
		return $this->render('admin/action/filter.tpl', $params);
	}

	public function getText() {
		$links = array(
			array(
				'ref' => $this->fullRef.'/add',
				'name' => 'Добавить запись'
			)
		);
		if ($this->get('security')->isDeveloper()) {
			$links[] =	array(
				'ref' => $this->fullRef.'/table',
				'name' => 'Настройка таблицы'
			);
		}
		$links[] =	array(
			'ref' => $this->fullRef.'/create',
			'name' => 'Создать таблицу'
		);
		$links[] =	array(
			'ref' => $this->fullRef.'/alter',
			'name' => 'Обновить таблицу'
		);
		$content = $this->getOperationsBar($links);
		if (!empty($this->dataTable->params['is_view'])) {
			$content .= $this->getTreeContent();
		} else {
			$content .= $this->getTableContent();
		}
		$content .= $this->_getFilterForm();
		return $content;
	}
}
