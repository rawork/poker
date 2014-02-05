<?php

namespace Fuga\GameBundle\Model;

class Timer {
	
	public  $holder = 'game-timer';
	private $handler;
	private $time;
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
		$seconds = $this->time - time();
		$minutes = floor($seconds / 60);
		$seconds = $seconds - ($minutes * 60);
		setcookie('gametimer', $this->holder, time()+$this->cookietime, '/');
		setcookie('timerhandler', $this->handler, time()+$this->cookietime, '/');
		setcookie('timerminute',  $minutes, time()+$this->cookietime, '/');
		setcookie('timersecond',  $seconds, time()+$this->cookietime, '/');
		
		return $this;
	}
	
	public function stop() {
		$this->holder = 'game-timer';
		$this->handler = null;
		$this->time = null;
		setcookie('gametimer', $this->holder, time()+$this->cookietime, '/');
		setcookie('timerhandler', '', time()-$this->cookietime, '/');
		setcookie('timerminute', 0, time()-$this->cookietime, '/');
		setcookie('timersecond', 0, time()-$this->cookietime, '/');
		
		return $this;
	}
	
	public function setHolder($name = null) {
		$this->holder = $name ?: 'game-timer';
	}
	
	public function getHolder() {
		return $this->holder; 
	}
	
}
