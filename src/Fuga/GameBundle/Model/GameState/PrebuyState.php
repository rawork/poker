<?php

namespace Fuga\GameBundle\Model\GameState;

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
				return $this->endRound();
			} else {
				return $this->endGame();
			}
		}
		
		return $this->game->getStateNo();
	}
	
	public function endRound() {
		$this->game->timer->stop();
		$this->game->gamer->buying = null;
		if ($this->game->gamer->chips > 0) {
			parent::endRound();
		} else {
			return $this->endGame();
		}
	}
	
}