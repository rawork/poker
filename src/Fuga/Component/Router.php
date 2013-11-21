<?php

namespace Fuga\Component;

use Fuga\Component\Exception\NotFoundHttpException;
	
class Router {
	
	private $container;
	private $url;
	private $params = array();
	private $paths = array();
	private $locales = array(array('name' => 'ru'));
	private $redirectCodes = array(
		'301' => 'Moved Permanently',
		'302' => 'Moved Temporarily',
	);
	
	public function __construct($container){
		$this->container = $container;
		if (!preg_match("/^".PRJ_REF.'/i', $_SERVER['REQUEST_URI'])) {
			$this->redirect(PRJ_REF.$_SERVER['REQUEST_URI']);
		}
		$this->url = str_replace(PRJ_REF, '', $_SERVER['REQUEST_URI']);
	}
	
	public function setLocale() {
		$locale = $this->container->get('util')->post('locale');
		if ( $locale
			&& $this->container->get('util')->session('locale', false, PRJ_LOCALE) != $locale) {
			$_SESSION['locale'] = $locale;
			$this->redirect($_SERVER['REQUEST_URI']);
		}
	}
	
	public function getLocales() {
		return $this->locales;
	}
	
	public function getPath($nativeUrl = null) {
		$url = $nativeUrl ?: $this->url;
		if (!isset($this->paths[$nativeUrl])) {
			$_SESSION['locale'] = isset($_SESSION['locale']) ? $_SESSION['locale'] : PRJ_LOCALE;
			if ($this->isPublic($url)) {
				foreach ($this->getLocales() as $locale) {
					if (stristr($url, '/'.$locale['name'].'/') 
						|| $this->container->get('util')->request('locale') == $locale['name']) 
				    {
						$_SESSION['locale'] = $locale['name'];
						$url = str_replace('/'.$locale['name'].'/', '/', $url);
						if (!$url)
							$url = '/';
					}
				}
			}
			$this->setParam('locale', $this->container->get('util')->session('locale', false, PRJ_LOCALE));
			$urlParts = explode('#', $url);
			if (!empty($urlParts[1])) {
				$this->setParam('ajaxmethod', $urlParts[1]);
			}
			$urlParts = explode('?', $urlParts[0]);
			if (!empty($urlParts[1])) {
				$this->setParam('query', $urlParts[1]);
			}
			$this->paths[$nativeUrl] = $urlParts[0]; 
		}
		
		return $this->paths[$nativeUrl];
	}
	
	public function isPublic($url = null) {
		$url = $url ?: $this->url;
		return !preg_match('/^\/(admin|adminajax|bundles)\//', $url);
	}
	
	public function isAdmin($url = null) {
		$url = $url ?: $this->url;
		return preg_match('/^\/(admin)\//', $url);
	}
	
	public function isAdminAjax($url = null) {
		$url = $url ?: $this->url;
		return preg_match('/^\/adminajax\//', $url);
	}
	
	/**
	 * Разбирает URL на части /Node/Action/Params
	 */
	public function getRoute($url = '/') {
		if ('/' == $url) {
			return array(
				'node' => '/',
				'action' => 'index',
				'params' => array()
			);
		} elseif (substr($url, -1) == '/') {
			$url = substr($url, 0, strlen($url)-1);
			$this->redirect($url, 301);
			exit();
		} elseif (preg_match('/^(\/[a-z0-9\-]+)+$/', $url, $matches)) {
			$path = explode('/', $url);
			array_shift($path);
			$node = array_shift($path);
			$action = !$path || is_numeric($path[0]) ? 'index' :array_shift($path);
			$params = $path;
			return array(
				'node' => $node,
				'action' => $action,
				'params' => $params
			);
		} else {
			throw new NotFoundHttpException('Несуществующая страница');
		}
	}

	public function setParams($url = null){
		$url = $this->getPath($url ?: $this->url);
		if ($this->isPublic($url)) {
			$route = $this->getRoute($url);
			$this->setParam('node', $route['node']);
			$this->setParam('action', $route['action']);
			$this->setParam('params', $route['params']);
			
		} elseif ($this->isAdmin($url)) {
			$urlParts = explode('/', $url);
			if (!empty($urlParts[2])) {
				$this->setParam('state', $urlParts[2]);  
			} else {
				$this->setParam('state', 'content');
			}
			if (!empty($urlParts[3])) {
				$this->setParam('module', $urlParts[3]);  
			} else {
				$this->setParam('module', '');
			}
			if (!empty($urlParts[4])) {
				$this->setParam('table', $urlParts[4]);  
			} else {
				$this->setParam('table', '');
			}
			if (!empty($urlParts[5])) {
				$this->setParam('action', $urlParts[5]);  
			} else {
				$this->setParam('action', 'index');
			}
			if (!empty($urlParts[6])) {
				$this->setParam('id', $urlParts[6]);  
			} else {
				$this->setParam('id', 0);
			}
		} else {	
			$this->setParam('uri', $this->getPath($url));
		}
	}
	
	public function setParam($name, $value) {
		$this->params[$name] = $value; 
	}
	
	public function hasParam($name) {
		return !empty($this->params[$name]); 
	}
	
	public function getParam($name) {
		return $this->params[$name]; 
	}
	
	public function redirect($url, $code = 302){
		header("HTTP/1.1 $code {$this->redirectCodes[$code]}");
		header('location:'.$url);
		exit;
	}
	
	public function reload() {
		$this->redirect($_SERVER['REQUEST_URI']);
	}
	
	public function generateUrl($node = '/', $action = 'index', $params = array()) {
		if ($node == '/') {
			return PRJ_REF.$node;
		}
		$path = array(PRJ_REF);
//		if ('ru' != $this->getParam('locale')) {
//			$path[] = $this->getParam('locale');
//		}
		$path[] = $node;
		if ($action != 'index') {
			$path[] = $action;
		}
		if (count($path)){
			$path = array_merge($path, $params);
		}
		return implode('/', $path);
	}
	
	public function isXmlHttpRequest() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' == $_SERVER['HTTP_X_REQUESTED_WITH'];
	}
	
}
