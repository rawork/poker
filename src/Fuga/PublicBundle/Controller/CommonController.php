<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;

class CommonController extends Controller {
	
	public function blockAction($params) {
		$item = $this->get('container')->getItem('page_block',"name='{$params[0]}' AND publish=1");
		
		return $item ? $item['content'] : '';
	}
	
	public function breadcrumbAction() {
		$node = $this->getManager('Fuga:Common:Page')->getCurrentNode();
		$nodes = $this->getManager('Fuga:Common:Page')->getPathNodes($node['id']);
		$action = $this->get('router')->getParam('action');	
		$params = $this->get('router')->getParam('params');
		if ($node['name'] == 'catalog' && isset($params[0])) {
			$nodes = array_merge($nodes, $this->getManager('Fuga:Common:Category')->getPathNodes($params[0]));
		}	
		
		return $this->render('common/breadcrumb.tpl', compact('nodes', 'action'));
	}
	
	private function getMapList($uri = 0) {
		$nodes = array();
		$items = $this->getManager('Fuga:Common:Page')->getNodes($uri);
		$block = strval($uri) == '0' ? '' :  '_sub';
		if (count($items)) {
			foreach ($items as $node) {
				$node['sub'] = '';
				if ($node['module_id']) {
					$controller = $this->get('container')->createController($node['module_id_path']);
					$node['sub'] = $controller->mapAction();
				}
				$node['sub'] .= $this->getMapList($node['id']);
				$nodes[] = $node;
			}
		}
		return $this->render('common/map.tpl', compact('nodes', 'block'));
	}

	public function mapAction() {
		return $this->getMapList();
	}
	
	public function subscribeAction() {
		$subscribe_message = '';
		if (isset($_SESSION['subscribe_message'])) {
			$subscribe_message = $_SESSION['subscribe_message'];
		}
		return $this->render('subscribe/form.tpl', compact('subscribe_message'));
	}
	
	public function subscriberesultAction() {
		parse_str($this->get('util')->post('formdata'));
		if (!$this->get('util')->valid_email($email)) {
			$message = array(
				'message' => 'Неправильный E-mail',
				'success' => false
			);
		} else {
			if ($subscribe_type == 2) {
				$message = $this->getManager('Fuga:Common:Maillist')->unsubscribe($email);
			} elseif ($subscribe_type == 1) {
				$message = $this->getManager('Fuga:Common:Maillist')->subscribe($email, $name, $lastname);
			}
		}
		return json_encode(array('content' => $this->render('subscribe/result.tpl', $message)));
	}
	
	public function formAction($params) {
		return $this->getManager('Fuga:Common:Form')->getForm($params[0]);
	}
	
	public function voteAction($params) {
		return $this->getManager('Fuga:Common:Vote')->getForm($params[0]);
	}
	
}