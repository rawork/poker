<?php

namespace Fuga\GameBundle\Model;

interface GameInterface {
	
	public function registerObserver(ObserverInterface $o);
	public function removeObserver(ObserverInterface $o);
	public function notifyObservers();
	
}
