<?php

namespace Fuga\GameBundle\Model;

class Rival {
	
	public $id;
	public $avatar;
	public $name;
	public $lastname;
	public $allin;
	public $bet;
	public $seat;
	public $position;
	public $chips;
	
	public function __construct(array $rival, RealGamer $gamer) {
		$this->id       = $rival['user_id'];
		$this->avatar   =  isset($rival['avatar_value']['extra']) 
				? $rival['avatar_value']['extra']['main']['path'] 
				: '/bundles/public/img/avatar_empty.png';
		$this->name     = $rival['name'];
		$this->lastname = $rival['lastname'];
		$this->bet      = $rival['bet'];
		$this->seat     = intval($rival['seat']);
		$this->position = $gamer->getRivalPosition($this->seat);
		$this->chips    = intval($rival['chips']);
	}
}