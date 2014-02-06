<?php

namespace Fuga\GameBundle\Model\State;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;

class JokerState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function buyChips() {
		$now = new \Datetime();
		if ($now > $this->game->stopbuytime) {
			$this->game->timer->stop();
			$this->game->gamer->buying = null;
			if ($this->game->gamer->chips > 0) {
				return $this->endRound();
			} else {
				return $this->endGame();
			}
		} else {
			$this->game->setTimer('nobuy');
			$this->game->setState(AbstractState::STATE_PREBUY);
		}
		
		return $this->game->getStateNo();
	}
}