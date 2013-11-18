<?php

namespace Fuga\Component\Cache;

class AbstractCache implements CacheInterface {
	
	public function clean($name){}
	public function get($name){}
public function set($name, $value){}
public function updated($name){}
public function isExpired($name){}
	public function setLifeTime($name){}
	public function setOption($name, $value){}
	
}