<?php

namespace Fuga\PokerBundle\Model;



class Deck {

	private $deck = array(
		array('name' => '2 diamonds',     'suit' => 1, 'weight' => 1),
		array('name' => '3 diamonds',     'suit' => 1, 'weight' => 2),
		array('name' => '4 diamonds',     'suit' => 1, 'weight' => 4),
		array('name' => '5 diamonds',     'suit' => 1, 'weight' => 8),
		array('name' => '6 diamonds',     'suit' => 1, 'weight' => 16),
		array('name' => '7 diamonds',     'suit' => 1, 'weight' => 32),
		array('name' => '8 diamonds',     'suit' => 1, 'weight' => 64),
		array('name' => '9 diamonds',     'suit' => 1, 'weight' => 128),
		array('name' => '10 diamonds',    'suit' => 1, 'weight' => 256),
		array('name' => 'jack diamonds',  'suit' => 1, 'weight' => 512),
		array('name' => 'queen diamonds', 'suit' => 1, 'weight' => 1024),
		array('name' => 'king diamonds',  'suit' => 1, 'weight' => 2048),
		array('name' => 'ace diamonds',   'suit' => 1, 'weight' => 4096),
		array('name' => '2 hearts',     'suit' => 2, 'weight' => 1),
		array('name' => '3 hearts',     'suit' => 2, 'weight' => 2),
		array('name' => '4 hearts',     'suit' => 2, 'weight' => 4),
		array('name' => '5 hearts',     'suit' => 2, 'weight' => 8),
		array('name' => '6 hearts',     'suit' => 2, 'weight' => 16),
		array('name' => '7 hearts',     'suit' => 2, 'weight' => 32),
		array('name' => '8 hearts',     'suit' => 2, 'weight' => 64),
		array('name' => '9 hearts',     'suit' => 2, 'weight' => 128),
		array('name' => '10 hearts',    'suit' => 2, 'weight' => 256),
		array('name' => 'jack hearts',  'suit' => 2, 'weight' => 512),
		array('name' => 'queen hearts', 'suit' => 2, 'weight' => 1024),
		array('name' => 'king hearts',  'suit' => 2, 'weight' => 2048),
		array('name' => 'ace hearts',   'suit' => 2, 'weight' => 4096),
		array('name' => '2 spade',     'suit' => 4, 'weight' => 1),
		array('name' => '3 spade',     'suit' => 4, 'weight' => 2),
		array('name' => '4 spade',     'suit' => 4, 'weight' => 4),
		array('name' => '5 spade',     'suit' => 4, 'weight' => 8),
		array('name' => '6 spade',     'suit' => 4, 'weight' => 16),
		array('name' => '7 spade',     'suit' => 4, 'weight' => 32),
		array('name' => '8 spade',     'suit' => 4, 'weight' => 64),
		array('name' => '9 spade',     'suit' => 4, 'weight' => 128),
		array('name' => '10 spade',    'suit' => 4, 'weight' => 256),
		array('name' => 'jack spade',  'suit' => 4, 'weight' => 512),
		array('name' => 'queen spade', 'suit' => 4, 'weight' => 1024),
		array('name' => 'king spade',  'suit' => 4, 'weight' => 2048),
		array('name' => 'ace spade',   'suit' => 4, 'weight' => 4096),
		array('name' => '2 clubs',     'suit' => 8, 'weight' => 1),
		array('name' => '3 clubs',     'suit' => 8, 'weight' => 2),
		array('name' => '4 clubs',     'suit' => 8, 'weight' => 4),
		array('name' => '5 clubs',     'suit' => 8, 'weight' => 8),
		array('name' => '6 clubs',     'suit' => 8, 'weight' => 16),
		array('name' => '7 clubs',     'suit' => 8, 'weight' => 32),
		array('name' => '8 clubs',     'suit' => 8, 'weight' => 64),
		array('name' => '9 clubs',     'suit' => 8, 'weight' => 128),
		array('name' => '10 clubs',    'suit' => 8, 'weight' => 256),
		array('name' => 'jack clubs',  'suit' => 8, 'weight' => 512),
		array('name' => 'queen clubs', 'suit' => 8, 'weight' => 1024),
		array('name' => 'king clubs',  'suit' => 8, 'weight' => 2048),
		array('name' => 'ace clubs',   'suit' => 8, 'weight' => 4096),
		array('name' => 'joker',   'suit' => 0, 'weight' => 8192),
	);
	
	public function get($shuffle = false) {
		$deck = $this->deck;
		if ($shuffle) {
			$this->shuffle($deck);
		}

		return $deck;
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