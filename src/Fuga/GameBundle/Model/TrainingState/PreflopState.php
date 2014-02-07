<?php

namespace Fuga\GameBundle\Model\TrainingState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;

class PreflopState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function makeBet($chips) {
		$this->game->maxbet = $this->game->acceptBet($this->game->gamer->bet($chips, $this->game->maxbet));

		foreach ($this->game->bots as $bot) {
			if ($bot->isActive()) {
				$isBet = $this->game->gamer->allin ? rand(1,3) == 2 : true;
				if ($isBet) {
					$this->game->acceptBet($bot->bet($chips, $this->game->maxbet));
				} else {
					$bot->cards = null;
				}
			}
		}
		$this->game->confirmBets();
		$combination = new Combination();
		$cards = $combination->get(array_merge($this->game->gamer->cards, $this->game->flop));
		$combinations = array();
		foreach ($cards['cards'] as $card) {
			$combinations[$card['name']] = 1;
		}
		$this->game->gamer->rank = $combination->rankName($cards['rank']);
		$this->game->gamer->combination = $combinations;
		if ($this->game->gamer->allin) {
			$this->game->maxbet = 0;
			$this->setWinner();
			$this->game->setState(AbstractState::STATE_SHOWDOWN);
		} else {
			$this->game->maxbet = 0;
			$this->game->setState(AbstractState::STATE_FLOP);
		}
		
		return $this->game->getStateNo();
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