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
				$this->game->deck->make();
				$this->game->gamer->cards = $this->game->deck->take(4);
				$botsWithCards = 0;
				foreach ($this->game->bots as $bot) {
					$bot->cards = $bot->isActive() ? $this->game->deck->take(4) : null;
				}
				$this->game->flop = $this->game->deck->take(3);
				$this->game->setTimer('change');
				$this->game->setState(self::STATE_CHANGE);
			} else {
				$this->game->stopTime();
				$this->game->gamer->cards = array();
				$this->game->setState(AbstractState::STATE_END);
			}
		} else {
			$this->game->setTimer('nobuy');
			$this->game->setState(self::STATE_PREBUY);
		}
		
		return $this->game->getStateNo();
	}
}