<?php

namespace Fuga\GameBundle\Model\TrainingState;

class FlopState extends AbstractState {
	
	public function __construct($game) {
		parent::__construct($game);
	}
	
	public function makeBet($chips) {
		$this->game->maxbet = $this->game->acceptBet($this->game->gamer->bet($chips, $this->game->maxbet));

		foreach ($this->game->bots as $bot) {
			if ($bot->isActive()) {
//				$isBet = $this->game->gamer->allin ? rand(1,3) == 2 : true;
				$isBet = rand(1,3) != 2;
				if ($isBet) {
					$this->game->acceptBet($bot->bet($chips, $this->game->maxbet));
				} else {
					$bot->cards = null;
				}
			}
		}
		$this->game->confirmBets();
		$this->game->gamer->allin = false;
		$this->game->maxbet = 0;
		$this->setWinner();
		$this->game->setState(AbstractState::STATE_SHOWDOWN);
		
		return $this->game->getStateNo();
	}
	
	public function checkBet(){
		$this->setWinner();
		$this->game->setState(AbstractState::STATE_SHOWDOWN);
	}
	
	public function foldCards() {
		$this->game->gamer->cards = null;
		foreach ($this->game->bots as $bot) {
			$this->game->acceptBet($bot->bet($this->game->minbet));
		}
		$this->game->confirmBets();
		$this->game->gamer->rank = null;
		$this->game->gamer->combination = null;
		$this->setWinner();
		$this->game->setState(AbstractState::STATE_SHOWDOWN);
	}
	
}