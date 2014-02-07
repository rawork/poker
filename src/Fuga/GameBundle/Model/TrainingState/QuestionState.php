<?php

namespace Fuga\GameBundle\Model\TrainingState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;

class QuestionState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function answerQuestion($answerNo, $question) {
		$this->game->timer->stop();
		$this->game->changes -= 1; 
		if ($answerNo == $this->game->gamer->question['answer']) {
			$this->game->gamer->cards[$this->game->gamer->change] = array_shift($this->game->deck->take(1));
		} else {
			$this->game->gamer->chips -= 1;
		}
		$this->game->gamer->change = null;
		$this->game->gamer->question = null;
		if ($this->game->gamer->chips > 0 && $this->game->changes > 0) {
			$this->game->setTimer('change');
			$this->game->setState(AbstractState::STATE_CHANGE);
		} elseif ($this->game->gamer->chips > 0) {
			$combination = new Combination();
			$cards = $combination->get($this->game->gamer->cards);
			$combinations = array();
			foreach ($cards['cards'] as $card) {
				$combinations[$card['name']] = 1;
			}
			$this->game->gamer->rank = $combination->rankName($cards['rank']);
			$this->game->gamer->combination = $combinations;
			$this->game->changes = 2;
			$this->game->setTimer('bet');
			$this->game->setState(AbstractState::STATE_PREFLOP);
		} else {
			return $this->endGame();
		}
		
		return true;
	}
	
}