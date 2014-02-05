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
		$this->game->winner = null;
		$this->game->combination = null;
		$this->game->flop   = null;
		$this->game->bets   = 0;
		$this->game->allin = false;
		$botsWithoutMoney = 0;
		foreach ($this->game->bots as $bot) {
			$bot->cards = null;
			$bot->emptyBet();
			if ($bot->chips > 0) {
				$bot->active = true;
			} else {
				$bot->active = false;
				$botsWithoutMoney++;
			}
		}
		$now = new \DateTime();
		$this->game->gamer->buying = $questions;
		if (!$this->game->existsBots()) {
			$this->game->timer->stop();
			$this->game->stopTime();
			$this->game->winner = null;
			$this->game->combination = null;
			$this->game->gamer->cards = array();
			$this->game->setState(AbstractState::STATE_END);
		} elseif (!$this->game->existsJoker()) {
			$this->game->setTimer('prebuy');
			$this->game->setState(self::STATE_JOKER);
		} elseif ($now > $this->game->stopbuytime) {
			$this->game->timer->stop();
			$this->game->gamer->buying = null;
			$this->game->deck->make();
			$this->game->gamer->cards = $this->game->deck->take(4);
			foreach ($this->game->bots as $bot) {
				$bot->cards = $bot->isActive() ? $this->game->deck->take(4) : null;
			}
			$this->game->flop = $this->game->deck->take(3);
			$this->game->setTimer('change');
			$this->game->setState(self::STATE_CHANGE);
		} else {
			$this->game->setTimer('nobuy');
			$this->game->setState(self::STATE_PREBUY);
		}
		
		return $this->game->getStateNo();
	}
	
}


