<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Exception\GameException;

class EndState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function nextGame($gamer) {
		throw new GameException('Игра окончена. Невозможно продолжить.');
	}
	
	public function endRound($gamer) {
		throw new GameException('Игра окончена. Невозможно закончить раунд.');
	}
	
}