<?php

namespace Fuga\GameBundle\Model;

use Fuga\GameBundle\Document\Gamer;

class Rival {
	
	public $id;
	public $name;
	public $lastname;
	public $allin;
	public $bet;
	public $seat;
	public $position;
	public $chips;
	public $cards;
	public $active;
	public $state;
	
	public function __construct(Gamer $rival, $position) {
		$this->id       = $rival->getUser();
		$this->avatar   = $rival->getAvatar();
		$this->name     = $rival->getName();
		$this->lastname = $rival->getLastname();
		$this->bet      = $rival->getBet();
		$this->seat     = $rival->getSeat();
		$this->position = $position;
		$this->chips    = $rival->getChips();
		$this->cards    = $rival->getCards();
		$this->active   = $rival->getActive();
		$this->state    = $rival->getState();
		$this->times    = $rival->getTimes();
		$this->allin    = $rival->getAllin();
	}
	
	public function isActive() {
		return $this->active;
	}
	
	public function isHere() {
		return $this->isActive() && $this->state == 1;
	}

}	