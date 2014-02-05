<?php

namespace Fuga\GameBundle\Model\State;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;

class PrebuyState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function buyChips() {
		$this->game->timer->stop();
		if (is_array($this->game->gamer->buying) && count($this->game->gamer->buying) > 0) {
			$this->game->gamer->question = array_shift($this->game->gamer->buying);
			$this->game->gamer->question['number'] = 3 - count($this->game->gamer->buying);
			$this->game->setTimer('buy');
			$this->game->setState(AbstractState::STATE_BUY);
		} else {
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
				$this->game->setState(AbstractState::STATE_CHANGE);
			} else {
				$this->game->stopTime();
				$this->game->gamer->cards = array();
				$this->game->setState(AbstractState::STATE_END);
			}
		}
		
		return $this->game->getStateNo();
	}
	
	public function nextGame() {
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
			$this->game->setState(AbstractState::STATE_CHANGE);
		} else {
			$this->game->stopTime();
			$this->game->gamer->cards = array();
			$this->game->setState(AbstractState::STATE_END);
		}
		
		return $this->game->getStateNo();
	}
	
}