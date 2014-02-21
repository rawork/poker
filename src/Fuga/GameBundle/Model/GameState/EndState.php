<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;

class EndState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function nextGame($gamer) {
		return self::STATE_END;
	}
	
	public function endRound($gamer) {
		return self::STATE_END;
	}
	
}