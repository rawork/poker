<?php

namespace Fuga\GameBundle\Model\State;

use Fuga\GameBundle\Model\GameInterface;

class EndState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function stopGame() {
		$this->game->timer->stop();
		$this->game->stopTime();
		$this->game->setState(AbstractState::STATE_BEGIN);
		
		return true;
	}
	
	
	
}