<?php

namespace Fuga\GameBundle\Model;

class Timer {
	
	public  $holder = 'game-timer';
	private $handler;
	private $time;
	
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
		setcookie('gametimer', $this->holder, time()+86400, '/');
		setcookie('timerhandler', $this->handler, time()+86400, '/');
		setcookie('timerminute',  $minutes, time()+86400, '/');
		setcookie('timersecond',  $seconds, time()+86400, '/');
		
		return $this;
	}
	
	public function stop() {
		$this->holder = 'game-timer';
		$this->handler = null;
		$this->time = null;
		setcookie('timerhandler', '', time()-86400, '/');
		setcookie('timerminute', 0, time()-86400, '/');
		setcookie('timersecond', 0, time()-86400, '/');
		
		return $this;
	}
	
	public function setHolder($name = null) {
		$this->holder = $name ?: 'game-timer';
	}
	
	public function getHolder() {
		return $this->holder; 
	}
	
}
