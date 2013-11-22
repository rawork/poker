<?php

namespace Fuga\PokerBundle\Model;



class Deck {

	private $defaultdeck = array(
		array('name' => '2_diams',     'suit' => 1, 'weight' => 1),
		array('name' => '3_diams',     'suit' => 1, 'weight' => 2),
		array('name' => '4_diams',     'suit' => 1, 'weight' => 4),
		array('name' => '5_diams',     'suit' => 1, 'weight' => 8),
		array('name' => '6_diams',     'suit' => 1, 'weight' => 16),
		array('name' => '7_diams',     'suit' => 1, 'weight' => 32),
		array('name' => '8_diams',     'suit' => 1, 'weight' => 64),
		array('name' => '9_diams',     'suit' => 1, 'weight' => 128),
		array('name' => '10_diams',    'suit' => 1, 'weight' => 256),
		array('name' => 'jack_diams',  'suit' => 1, 'weight' => 512),
		array('name' => 'queen_diams', 'suit' => 1, 'weight' => 1024),
		array('name' => 'king_diams',  'suit' => 1, 'weight' => 2048),
		array('name' => 'ace_diams',   'suit' => 1, 'weight' => 4096),
		array('name' => '2_hearts',       'suit' => 2, 'weight' => 1),
		array('name' => '3_hearts',       'suit' => 2, 'weight' => 2),
		array('name' => '4_hearts',       'suit' => 2, 'weight' => 4),
		array('name' => '5_hearts',       'suit' => 2, 'weight' => 8),
		array('name' => '6_hearts',       'suit' => 2, 'weight' => 16),
		array('name' => '7_hearts',       'suit' => 2, 'weight' => 32),
		array('name' => '8_hearts',       'suit' => 2, 'weight' => 64),
		array('name' => '9_hearts',       'suit' => 2, 'weight' => 128),
		array('name' => '10_hearts',      'suit' => 2, 'weight' => 256),
		array('name' => 'jack_hearts',    'suit' => 2, 'weight' => 512),
		array('name' => 'queen_hearts', 'suit' => 2, 'weight' => 1024),
		array('name' => 'king_hearts',  'suit' => 2, 'weight' => 2048),
		array('name' => 'ace_hearts',   'suit' => 2, 'weight' => 4096),
		array('name' => '2_spades',     'suit' => 4, 'weight' => 1),
		array('name' => '3_spades',     'suit' => 4, 'weight' => 2),
		array('name' => '4_spades',     'suit' => 4, 'weight' => 4),
		array('name' => '5_spades',     'suit' => 4, 'weight' => 8),
		array('name' => '6_spades',     'suit' => 4, 'weight' => 16),
		array('name' => '7_spades',     'suit' => 4, 'weight' => 32),
		array('name' => '8_spades',     'suit' => 4, 'weight' => 64),
		array('name' => '9_spades',     'suit' => 4, 'weight' => 128),
		array('name' => '10_spades',    'suit' => 4, 'weight' => 256),
		array('name' => 'jack_spades',  'suit' => 4, 'weight' => 512),
		array('name' => 'queen_spades', 'suit' => 4, 'weight' => 1024),
		array('name' => 'king_spades',  'suit' => 4, 'weight' => 2048),
		array('name' => 'ace_spades',   'suit' => 4, 'weight' => 4096),
		array('name' => '2_clubs',     'suit' => 8, 'weight' => 1),
		array('name' => '3_clubs',     'suit' => 8, 'weight' => 2),
		array('name' => '4_clubs',     'suit' => 8, 'weight' => 4),
		array('name' => '5_clubs',     'suit' => 8, 'weight' => 8),
		array('name' => '6_clubs',     'suit' => 8, 'weight' => 16),
		array('name' => '7_clubs',     'suit' => 8, 'weight' => 32),
		array('name' => '8_clubs',     'suit' => 8, 'weight' => 64),
		array('name' => '9_clubs',     'suit' => 8, 'weight' => 128),
		array('name' => '10_clubs',    'suit' => 8, 'weight' => 256),
		array('name' => 'jack_clubs',  'suit' => 8, 'weight' => 512),
		array('name' => 'queen_clubs', 'suit' => 8, 'weight' => 1024),
		array('name' => 'king_clubs',  'suit' => 8, 'weight' => 2048),
		array('name' => 'ace_clubs',   'suit' => 8, 'weight' => 4096),
		array('name' => 'joker',   'suit' => 0, 'weight' => 8192),
	);
	private $deck;
	
	public function __construct() {
		$this->make(true);
	}
	
	public function make($shuffle = false) {
		$this->deck = $this->defaultdeck;
		if ($shuffle) {
			$this->shuffle($this->deck);
		}

		return $this->deck;
	}
	
	public function give($quantity) {
		$suite = array();
		for ($i = 0; $i < $quantity; $i++) {
			$suite[] = array_shift($this->deck);
		}
		
		return $suite;
	}
	
	public function shuffle(array &$deck) {
		$times = rand(20,30);
		$res = false;
		for ($i = 0; $i < $times; $i++) {
			$res = shuffle($deck);
		}
		
		return $res;
	}
	
}