<?php

namespace Fuga\GameBundle\Model\State;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;

class ChangeState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function changeCards($cardNo, $question) {
		$this->game->timer->stop();
		$this->game->gamer->change = $cardNo;
		$this->game->gamer->question = $question;
		$this->game->setTimer('answer');
		$this->game->setState(AbstractState::STATE_QUESTION);
	}
	
	public function noChangeCards() {
		$this->game->timer->stop();
		$this->game->changes = 2;
		$combination = new Combination();
		$cards = $combination->get($this->game->gamer->cards);
		$combinations = array();
		foreach ($cards['cards'] as $card) {
			$combinations[$card['name']] = 1;
		}
		$this->game->gamer->rank = $combination->rankName($cards['rank']);
		$this->game->gamer->combination = $combinations;
		$this->game->setTimer('bet');
		$this->game->setState(AbstractState::STATE_PREFLOP);
	}
	
}