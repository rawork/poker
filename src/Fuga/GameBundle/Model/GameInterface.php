<?php

namespace Fuga\GameBundle\Model;

interface GameInterface {
	
	public function setState($state);
	public function getState();
	public function startTime();
	public function stopTime();
	public function syncTime();
	public function setTimer($name);
	
}
