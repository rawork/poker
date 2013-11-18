<?php

namespace Fuga\Component\Templating;

class SmartyTemplating implements TemplatingInterface {
	
	private $engine;
	private $basePath		= '/app/Resources/views/';
	private $baseCachePath	= '/app/cache/smarty/';
	private $realPath		= '';
	
	public function __construct() {
		$this->engine = new \Smarty();
		$this->realPath = PRJ_DIR.$this->basePath;
		$this->engine->template_dir = PRJ_DIR.$this->basePath;
		$this->engine->compile_dir = PRJ_DIR.$this->baseCachePath;
		$this->engine->compile_check = true;
//		$this->engine->caching = 1;
//		$this->engine->cache_lifetime = 604800;
		$this->engine->debugging = false;
	}
	
	public function assign($params) {
		foreach ($params as $paramName => $paramValue) {
			$this->engine->assign($paramName, $paramValue);
		}
	}
	
	public function render($template, $params = array(), $silent = false) {
		if (empty($template)) {
			throw new \Exception('Для обработки передан шаблон без названия');
		}
		$template = str_replace(array($this->realPath, $this->basePath), '', $template);
		if ($this->exists($template)) {
			$this->assign($params);
			return $this->engine->fetch($template);
		} elseif ($silent) {
			return false;
		} else {	
			throw new \Exception('Несуществующий шаблон "'.$template.'"');
		}
	}
	
	public function exists($template) {
		if (preg_match('/^var:(.)+/i', $template)) {
			$template = str_replace('var:', '', $template);
			return isset($GLOBALS['tplvar_'.$template]);
		} else {
			return file_exists($this->realPath.$template);
		}
	}
	
	public function clearTpl() {
		$this->engine->clear_compiled_tpl();
	}
	
	public function clearCache($template = '') {
		if ($template) {
			$this->engine->clear_cache($template);
		} else {
			$this->engine->clear_all_cache();
		}
		
	}
	
}
