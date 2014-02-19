<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;

class EndState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function nextGame($gamer) {
		$this->game->container->get('log')->write('Игра окончена. Невозможно продолжить.');
	}
	
	public function endRound($gamer) {
		$this->game->container->get('log')->write('Игра окончена. Невозможно закончить раунд.');
	}
	
}