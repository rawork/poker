<?php

namespace Fuga\GameBundle\Model;

class GameTimer {
	
	public  $holder = 'game-timer';
	private $handler = '';
	private $time = 0;
	private $cookietime = 7776000;
	
	public function set($handler, $holder, $time = null) {
		$this->holder = $holder;
		$this->handler = $handler;
		$this->time = $time == null ? time() : time() + $time;
		
		return $this;
	}
	
	public function start() {
		if (!$this->handler || !$this->time) {
			return $this;
		}
		$date = new \DateTime();
		$date->setTimestamp($this->time);
		setcookie('timerholder', $this->holder, time()+$this->cookietime, '/');
		setcookie('timerhandler', $this->handler, time()+$this->cookietime, '/');
		setcookie('timerstop',  $date->format('c'), time()+$this->cookietime, '/');
		
		return $this;
	}
	
	public function stop() {
		$this->holder = 'game-timer';
		$this->handler = '';
		$this->time = 0;
		setcookie('timerholder', $this->holder, time()+$this->cookietime, '/');
		setcookie('timerhandler', $this->handler, time()+$this->cookietime, '/');
		setcookie('timerstop', $this->time, time()+$this->cookietime, '/');
		
		return $this;
	}
	
	public function setHolder($name = null) {
		$this->holder = $name ?: 'game-timer';
	}
	
	public function getHolder() {
		return $this->holder; 
	}
	
}
