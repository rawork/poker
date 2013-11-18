<?php

namespace Fuga\Component\Templating;

class TwigTemplating implements TemplatingInterface {
	
	private $engine;
	private $basePath		= '/app/Resources/views/';
	private $baseCachePath	= '/app/cache/twig';
	private $realPath		= '';
	
	public function __construct() {
		$this->realPath = PRJ_DIR.$this->basePath;
		$loader = new \Twig_Loader_Filesystem($this->realPath);
		$this->engine = new \Twig_Environment($loader, array(
			'cache' => PRJ_DIR.$this->baseCachePath,
			'auto_reload' => true,
		));
	}
	
	public function assign($params = array()) {
		foreach ($params as $paramName => $paramValue) {
			$this->engine->addGlobal($paramName, $paramValue);
		}
	}
	
	public function render($template, $params = array(), $silent = false) {
		if (empty($template)) {
			throw new \Exception('Для обработки передан шаблон без названия');
		}
		$template = str_replace(array($this->realPath, $this->basePath), '', $template);
		if ($this->exists($template)) {
			return $this->engine->render($template, $params);
		} elseif ($silent) {
			return false;
		} else {	
			throw new \Exception('Несуществующий шаблон "'.$template.'"');
		}
	}
	
	public function exists($template) {
		return file_exists($this->realPath.$template);
	}
	
}
