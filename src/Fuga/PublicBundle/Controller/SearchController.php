<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

// TODO Cache of search results

class SearchController extends PublicController {
	
	function __construct() {
		parent::__construct('search');
	}

	function indexAction() {
		$searchText = $this->get('util')->request('text');
		
		if ($searchText) {
			$results = $this->get('search')->getResults($searchText);
			if (count($results)) {
				$this->page = $this->get('util')->request('page', true, 1);
				$maxPerPage = 20;
				$pagesQuantity = ceil(count($results)/$maxPerPage);
				$ptext = '';
				if ($pagesQuantity > 1){
					$ptext = '<div>';
					if ($this->page > 1) {
						$ref = '?text='.urlencode($this->get('util')->request('text')).'&page='.($this->page-1);
						$ptext .= '<a title="назад" href="'.$ref.'">&larr;</a>';
					}
					for ($i = 1; $i<=$pagesQuantity; $i++){
						$ptext .= $i == $this->page ? ' '.$i.' ' : ' <a href="?text='.urlencode($this->get('util')->request('text')).'&page='.$i.'">'.$i.'</a> ';
					}
					if ($this->page < $pagesQuantity) {
						$ref = '?text='.urlencode($this->get('util')->request('text')).'&page='.($this->page+1);
						$ptext .= '<a title="вперед" href="'.$ref.'">&rarr;</a>';
					}
					$ptext .= '</div>';
					
				}
				if ($this->page == $pagesQuantity &&  (sizeof($results) % $maxPerPage) > 0) {
					$max_per_page_cur = count($results) % $maxPerPage;
				} else {
					$max_per_page_cur = $maxPerPage;
				}
				$items = array();
				for ($i = 1; $i <= $max_per_page_cur; $i++) {
					$j = $i+($this->page-1)*$maxPerPage;
					$results[$j-1]['num'] = $j;
					$items[] = $results[$j-1];
				}
				$content .= $this->render('search/list.tpl', compact('ptext', 'items', 'searchText'));
			} else {
				$content .= $this->render('search/empty.tpl', compact('searchText'));
			}
		}
		$content = $this->render('search/form.tpl', compact('searchText')).$content;
		return $content;
	}
}
