<?php

namespace Fuga\GameBundle\Model\TrainingState;

use Fuga\GameBundle\Model\GameInterface;

class EndState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
}