<?php

namespace Fuga\GameBundle\Model\State;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;

class BuyState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function answerBuyQuestion($answerNo) {
		if (is_array($this->game->gamer->question)) {
			if ($answerNo == $this->game->gamer->question['answer']) {
				$this->game->gamer->giveChips($this->game->minbet);
				$this->game->question = null;
			}
		}
		if (is_array($this->game->gamer->buying) && count($this->game->gamer->buying) > 0) {
			$this->game->gamer->question = array_shift($this->game->gamer->buying);
			$this->game->gamer->question['number'] = 3 - count($this->game->gamer->buying);
		} else {
			$this->nextGame();
		}
		
		return $this->game->getStateNo();
	}
	
	public function nextGame() {
		$this->game->timer->stop();
		$this->game->gamer->buying = null;
		if ($this->game->gamer->chips > 0) {
			return $this->endRound();
		} else {
			return $this->endGame();
		}
		
		return $this->game->getStateNo();
	}
	
}
