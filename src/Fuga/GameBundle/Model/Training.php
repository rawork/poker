<?php

namespace Fuga\GameBundle\Model;

class Training {
	
	const STATE_CHANGE  = 1;
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
		$this->createBots();
	}
	
	public function createGamer($gamer) {
		$this->gamer = array(
			'id'      => 100,
			'avatar'  => $gamer['avatar_value']['extra']['main']['path'],
			'name'    => $gamer['name'],
			'lastname'=> $gamer['lastname'],
			'chips'   => 99,
			'bet'     => 0,
			'status'  => 1,
			'state'   => 0,
			'seat'    => 1,
			'cards'   => $this->deck->give(4),
		);
	}
	
	private function createBots() {
		for ($i = 2; $i < 5; $i++) {
			$this->bots[$i] = array(
				'id'      => $i,
				'avatar'  => '/bundles/public/img/bot.jpg',
				'name'    => 'Компьютер',
				'lastname'=> '',
				'chips'   => 99,
				'bet'     => 0,
				'status'  => 1,
				'state'   => 0,
				'seat'    => $i,
				'cards'   => $this->deck->give(4),
			);
		}
	}
	
	public function createBoard() {
		$this->board = array(
			'fromtime' => date('Y-m-d H:i:s'),
			'bank'     => 4,
			'bets'     => 0,
			'maxbet'   => 0,
			'minbet'   => 1,
			'allin'   => 0,
			'hour'     => 0,
			'minute'   => 0,
			'second'   => 0,
			'timerminute'   => 0,
			'timersecond'   => 30,
			'timerfunc'   => 'onClickNoChange',
			'flop'     => $this->deck->give(3),
			'status' => 1,
			'state'  => 1,
		);
	}
	
}
