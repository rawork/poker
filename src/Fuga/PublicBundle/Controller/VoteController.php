<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class VoteController extends  PublicController {
    
    public function __construct() {
		parent::__construct('vote');
	}
    
    public function indexAction() {
		$key = $this->get('container')->getItem('vote_code', "code='".$this->get('util')->request('key')."'");
        if (isset($key['node_id'])) {
			$ids = array_keys($this->get('container')->getItems('catalog_blagodom', 'publish=1 AND node_id='.$key['node_id']));
			$items = $this->get('container')->getItems('catalog_present', 'publish=1 AND blagodom_id IN('.implode(',', $ids).')');
			foreach ($items as &$item) {
				$item['blagodom_id_value']['item']['node_id_value'] = $this->getManager('Fuga:Common:Page')->getNode($item['blagodom_id_value']['item']['node_id']);
			}
			unset($item);
		}
		if (isset($key['is_used']) && 1 == $key['is_used']) {
			unset($key);
			$this->get('log')->write('Повторный вход на страницу голосования');
			$this->get('container')->setVar('javascript', true);
		}
		$list = $this->get('templating')->render('catalog/list.tpl', compact('items', 'key', 'message'));
		
        return $this->get('templating')->render('vote/index.tpl', compact('list', 'key'));
    }
	
	public function presentAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		$key = $this->get('container')->getItem('vote_code', "is_used<>1 AND code='".$this->get('util')->post('key')."'");
		if (!$key) {
			return json_encode(array('message' => $this->t('Thanks again'), 'error' => true));
		}
		$present_id = $this->get('util')->post('present_id');
		$this->get('container')->updateItem('vote_code', 
			array('is_used' => 1),
			array('id' => $key['id'])
		);
		$this->get('connection')->executeUpdate('UPDATE catalog_present SET weight = weight + 1 WHERE id = ?', array($present_id));
		$item = $this->get('container')->getItem('catalog_present', $present_id);
		$this->get('mailer')->send(
			'Новый голос за подарок на сайте Анкор. Добро 2013',
			$this->render('mail/vote.tpl', compact('item', 'key')),
			array(ADMIN_EMAIL)
		);
		
		return json_encode(array('message' => $this->t('Thank you for taking part in the action'), 'error' => false));
	}
	
}