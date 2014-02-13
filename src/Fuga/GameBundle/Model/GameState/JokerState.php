<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;

class JokerState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function buyChips($gamer) {
		if (!$this->game->lock($gamer->getId())) {
			return $this->game->getStateNo();
		}
		$now = new \Datetime();
		if ($now > $this->game->stopbuytime) {
			if (!$this->game->existsGamers()) {
				$this->game->removeTimer();
				$this->game->stopTime();
				$this->game->setState(self::STATE_END);
			} else {
				$this->game->setTimer('next');
				$this->game->startTimer();
				$this->game->setState(self::STATE_ROUND_END);
			}
		} else {
			$this->game->setTimer('buy');
			$this->game->startTimer();
			$this->game->setState(AbstractState::STATE_BUY);
		}
		$this->game->save();
		$this->game->unlock($gamer->getId());
		
		return $this->game->getStateNo();
	}
}