<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class CatalogController extends PublicController {
    
    public function __construct() {
		parent::__construct('catalog');
	}
    
    public function indexAction() {
		$node = $this->getManager('Fuga:Common:Page')->getCurrentNode();
		$items = $this->get('container')->getItems('catalog_blagodom', 'publish=1 AND node_id='.$node['id']);
		
		if ($this->get('router')->getParam('locale') == 'ru') {
			if ('ukraine' == $node['name']) {
				$this->get('container')->setVar('title', 'Благополучатель в Украине');
				$this->get('container')->setVar('h1', 'Благополучатель в Украине');
			} elseif ('kazahstan' == $node['name']) {
				$this->get('container')->setVar('title', 'Благополучатель в Казахстане');
				$this->get('container')->setVar('h1', 'Благополучатель в Казахстане');
			}
		}	
        return $this->get('templating')->render('catalog/index.tpl', compact('items'));
    }
    
	public function advicesAction($params) {
		if (empty($params[0])) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		$item = $this->get('container')->getItem('catalog_present', intval($params[0]));
		$lastId = 0;
		if (!$item) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		$messages = $this->get('container')->getItems('catalog_advice', 'publish=1 AND advice_id=0 AND present_id='.$item['id'], null, '5');
		if ($messages) {
			foreach ($messages as &$message) {
				$message['children'] = $this->get('container')->getItems('catalog_advice', 'publish=1 AND advice_id='.$message['id'], 'id ASC');
			}
			$lastId = $message['id'];
			unset($message);
		}
		$this->get('container')->setVar('title', $this->t('Recommendation').' - '.$item['name']);
		$this->get('container')->setVar('h1', $this->t('Recommendation').' - '.$item['name']);
		$this->get('container')->setVar('colnum', '6');
		$this->get('container')->setVar('advice', true);
		
		return $this->get('templating')->render('catalog/advices.tpl', compact('item', 'messages', 'lastId'));
	}
	
	public function listAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		$id = $this->get('util')->post('id');
		$items = $this->get('container')->getItems('catalog_present', 'publish=1 AND blagodom_id='.$id);
		foreach ($items as &$item) {
			$item['blagodom_id_value']['item']['node_id_value'] = $this->getManager('Fuga:Common:Page')->getNode($item['blagodom_id_value']['item']['node_id']);
		}
		unset($item);
		$item = $this->get('container')->getItem('catalog_blagodom', $id);

		return json_encode(array(
			'header' => $this->render('catalog/description.tpl', compact('item')),
			'content' => $this->render('catalog/list.tpl', compact('items')), 
			'footer1' => '<a class="pull-left btn btn-success btn-lg" href="javascript:void(0)" onclick="sendVote()"><img src="'.PRJ_REF.'/bundles/public/img/man-small-white.png"> '.$this->t('I give').'</a>',
			'footer' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true"><img src="'.PRJ_REF.'/bundles/public/img/man-small-white.png"> '.$this->t('Close').'</a>',
		));
	}
    
}