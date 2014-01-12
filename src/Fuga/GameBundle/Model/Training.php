<?php

namespace Fuga\GameBundle\Model;

class Training {
	
	const STATE_CHANGE  = 1;
	const STATE_QUESTION= 11;
	const STATE_PREFLOP = 2;
	const STATE_FLOP    = 3;
	const STATE_WIN     = 4;
	const STATE_BUY     = 5;
	
	public $deck;
	public $bots;
	public $gamer;
	public $board;
	
	public function __construct() {
		$this->deck = new Deck();
	}
	
	public function createGamer($gamer) {
		$this->gamer = new TrainingGamer($gamer);
		$this->gamer->cards = $this->deck->give(4);
		$this->gamer->position = $this->getPosition(6, 6);
	}
	
	public function createBots($n = 4) {
		for ($i = 1; $i <= $n; $i++) {
			$bot = new Bot($i);
			$bot->cards = $this->deck->give(4);
			$bot->position = $this->getPosition($i, $n);
			$this->bots[] = $bot;
		}
	}
	
	public function createBoard($user_id) {
		$this->board = new Board($user_id);
		$this->board->flop = $this->deck->give(3);
		$this->board->bank = 4;
	}
	
	public function getPosition($seat, $quantity) {
		if ($seat == 6) {
			return 0;
		}
		
		switch ($quantity) {
			case 1:
				return 2;
			case 2:
			case 3:
				return $seat + 1;
			default:
				return $seat;
		}
	}
	
}
