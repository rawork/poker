<?php

namespace Fuga\GameBundle\Model\State;

use Fuga\GameBundle\Model\GameInterface;

class ShowdownState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function distributeWin($questions) {
		$bank = $this->game->takeBank();
		$numWin = count($this->game->winner);
		$share = $numWin ? ceil($bank / $numWin) : $bank;
		foreach ($this->game->winner as $winner) {
			if ($winner['position'] == 0) {
				$this->game->gamer->giveChips($share);
				break;
			}
			foreach ($this->game->bots as $bot) {
				if ($bot->position == $winner['position']) {
					$bot->giveChips($share);
					break;
				}
			}
		}
		$this->game->gamer->checkActive();
		$this->game->gamer->emptyBet();
		$this->game->gamer->cards  = null;
		$this->game->gamer->rank = null;
		$this->game->gamer->combination = null;
		$this->game->gamer->winner = false;
		$this->game->winner = null;
		$this->game->combination = null;
		$this->game->flop   = null;
		$this->game->bets   = 0;
		$this->game->maxbet = 0;
		foreach ($this->game->bots as $bot) {
			$bot->cards = null;
			$bot->emptyBet();
			$bot->active = $bot->chips > 0;
		}
		$now = new \DateTime();
		$this->game->gamer->buying = $questions;
		if (!$this->game->existsBots()) {
			$this->endGame();
		} elseif (!$this->game->existsJoker()) {
			$this->game->setTimer('prebuy');
			$this->game->setState(AbstractState::STATE_JOKER);
		} elseif ($now > $this->game->stopbuytime) {
			return $this->endRound();
		} else {
			$this->game->setTimer('nobuy');
			$this->game->setState(AbstractState::STATE_PREBUY);
		}
		
		return $this->game->getStateNo();
	}
	
}


