<?php

namespace Fuga\GameBundle\Model\State;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;

class PreflopState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function makeBet($chips) {
		
	}
	
	public function checkBet(){
		
	}
	
	public function allinBet() {
		
	}
	
	public function foldCards() {
		$this->game->gamer->cards = null;
		foreach ($this->game->bots as $bot) {
			$this->acceptBet($bot->bet($this->game->minbet));
		}
		$this->game->confirmBets();
		$this->setWinner();
		$this->setState(self::STATE_SHOWDOWN);
	}
	
	private function setWinner() {
		$combination = new Combination();
		$suites = array();
		foreach ($this->game->bots as $bot) {
			if (!$bot->cards) {
				continue;
			}
			$cards = $combination->get(array_merge($bot->cards, $this->game->flop));
			$cards['position'] = $bot->position;
			$cards['name'] = $combination->rankName($cards['rank']);
			$suites[] = $cards;
		}
		if ($this->game->gamer->cards) {
			$cards = $combination->get(array_merge($this->game->gamer->cards, $this->game->flop));
			$cards['position'] = $this->game->gamer->position;
			$cards['name'] = $combination->rankName($cards['rank']);
			$suites[] = $cards;
		}
		$winners = $combination->compare($suites);
		$this->game->winner = $winners;
		$combinations = array();
		foreach ($winners as $winner) {
			foreach ($winner['cards'] as $card) {
				$combinations[$card['name']] = 1;
			}
		}
		$this->game->combination = $combinations;
		$this->game->setTimer('distribute');
	}
	
}