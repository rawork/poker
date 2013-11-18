<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class NewsController extends PublicController {
	
	public function __construct() {
		parent::__construct('news');
	}
	
	public function indexAction() {
		$node = $this->getManager('Fuga:Common:Page')->getCurrentNode();
		$page = $this->get('util')->request('page', true, 1);
		$paginator = $this->get('paginator');
		$paginator->paginate(
				$this->get('container')->getTable('news_news'),
				$this->get('router')->generateUrl($node['name']).'?page=###',
				'publish=1 AND node_id='.$node['id'],					 		  
				$this->getParam('per_page'),
				$page
		);
		$paginator->setTemplate('public');
		$items = $this->get('container')->getItems('news_news', 'publish=1 AND node_id='.$node['id'], null, $paginator->limit);
		$more = $this->get('container')->getItems('news_more', 'publish=1');
		
		return $this->get('templating')->render('news/index.tpl', compact('items', 'more', 'paginator'));
	}
	
	public function lentaAction() {
		$items = $this->get('container')->getItems('news_news', 'publish=1', null, $this->getParam('per_lenta'));
		foreach ($items as &$item) {
			$item['preview'] = $this->get('util')->cut_text($item['preview'], 170);
		}
		
		return $this->get('templating')->render('news/lenta.tpl', compact('items'));
	}
	
	public function readAction($params) {
		if (!isset($params[0])) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		$node = $this->getManager('Fuga:Common:Page')->getCurrentNode();
		$news = $this->get('container')->getItem('news_news', 'id='.$params[0].' AND publish=1 AND node_id='.$node['id']);
		if (!$news) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		$this->get('container')->setVar('title', $news['name']);
		$this->get('container')->setVar('h1', $news['name']);
		
		return $this->get('templating')->render('news/read.tpl', compact('news'));
	}
	
}