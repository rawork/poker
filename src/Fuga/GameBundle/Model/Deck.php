<?php

namespace Fuga\GameBundle\Model;

class Deck {

	private $defaultdeck = array(
		array('name' => '2_diams',      'suit' => 1, 'weight' => 2),
		array('name' => '3_diams',      'suit' => 1, 'weight' => 3),
		array('name' => '4_diams',      'suit' => 1, 'weight' => 4),
		array('name' => '5_diams',      'suit' => 1, 'weight' => 5),
		array('name' => '6_diams',      'suit' => 1, 'weight' => 6),
		array('name' => '7_diams',      'suit' => 1, 'weight' => 7),
		array('name' => '8_diams',      'suit' => 1, 'weight' => 8),
		array('name' => '9_diams',      'suit' => 1, 'weight' => 9),
		array('name' => '10_diams',     'suit' => 1, 'weight' => 10),
		array('name' => 'jack_diams',   'suit' => 1, 'weight' => 11),
		array('name' => 'queen_diams',  'suit' => 1, 'weight' => 12),
		array('name' => 'king_diams',   'suit' => 1, 'weight' => 13),
		array('name' => 'ace_diams',    'suit' => 1, 'weight' => 14),
		array('name' => '2_hearts',     'suit' => 2, 'weight' => 2),
		array('name' => '3_hearts',     'suit' => 2, 'weight' => 3),
		array('name' => '4_hearts',     'suit' => 2, 'weight' => 4),
		array('name' => '5_hearts',     'suit' => 2, 'weight' => 5),
		array('name' => '6_hearts',     'suit' => 2, 'weight' => 6),
		array('name' => '7_hearts',     'suit' => 2, 'weight' => 7),
		array('name' => '8_hearts',     'suit' => 2, 'weight' => 8),
		array('name' => '9_hearts',     'suit' => 2, 'weight' => 9),
		array('name' => '10_hearts',    'suit' => 2, 'weight' => 10),
		array('name' => 'jack_hearts',  'suit' => 2, 'weight' => 11),
		array('name' => 'queen_hearts', 'suit' => 2, 'weight' => 12),
		array('name' => 'king_hearts',  'suit' => 2, 'weight' => 13),
		array('name' => 'ace_hearts',   'suit' => 2, 'weight' => 14),
		array('name' => '2_spades',     'suit' => 4, 'weight' => 2),
		array('name' => '3_spades',     'suit' => 4, 'weight' => 3),
		array('name' => '4_spades',     'suit' => 4, 'weight' => 4),
		array('name' => '5_spades',     'suit' => 4, 'weight' => 5),
		array('name' => '6_spades',     'suit' => 4, 'weight' => 6),
		array('name' => '7_spades',     'suit' => 4, 'weight' => 7),
		array('name' => '8_spades',     'suit' => 4, 'weight' => 8),
		array('name' => '9_spades',     'suit' => 4, 'weight' => 9),
		array('name' => '10_spades',    'suit' => 4, 'weight' => 10),
		array('name' => 'jack_spades',  'suit' => 4, 'weight' => 11),
		array('name' => 'queen_spades', 'suit' => 4, 'weight' => 12),
		array('name' => 'king_spades',  'suit' => 4, 'weight' => 13),
		array('name' => 'ace_spades',   'suit' => 4, 'weight' => 14),
		array('name' => '2_clubs',      'suit' => 8, 'weight' => 2),
		array('name' => '3_clubs',      'suit' => 8, 'weight' => 3),
		array('name' => '4_clubs',      'suit' => 8, 'weight' => 4),
		array('name' => '5_clubs',      'suit' => 8, 'weight' => 5),
		array('name' => '6_clubs',      'suit' => 8, 'weight' => 6),
		array('name' => '7_clubs',      'suit' => 8, 'weight' => 7),
		array('name' => '8_clubs',      'suit' => 8, 'weight' => 8),
		array('name' => '9_clubs',      'suit' => 8, 'weight' => 9),
		array('name' => '10_clubs',     'suit' => 8, 'weight' => 10),
		array('name' => 'jack_clubs',   'suit' => 8, 'weight' => 11),
		array('name' => 'queen_clubs',  'suit' => 8, 'weight' => 12),
		array('name' => 'king_clubs',   'suit' => 8, 'weight' => 13),
		array('name' => 'ace_clubs',    'suit' => 8, 'weight' => 14),
		array('name' => 'joker',        'suit' => 0, 'weight' => 15),
	);
	private $deck;
	
	public function __construct() {
		$this->make();
	}
	
	public function make($shuffle = true) {
		$this->deck = $this->defaultdeck;
		if ($shuffle) {
			$this->shuffle($this->deck);
		}

		return $this->deck;
	}
	
	public function card($name) {
		foreach ($this->defaultdeck as $card) {
			if ($name == $card['name']) {
				return $card;
			}
		}
		
		throw new \Exception('card with name "'.$name.'" not found');
	}
	
	public function take($quantity = null) {
		$suite = array();
		if (!$quantity) {
			$suite = $this->deck;
			$this->deck = null;
		} else {
			for ($i = 0; $i < $quantity; $i++) {
				if (count($this->deck) > 0) {
					$suite[] = array_shift($this->deck);
				} else {
					$this->deck = null;
					return array();
				}
			}
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
	
	public function names($json = false) {
		$names = array();
		foreach ($this->defaultdeck as $card) {
			$names[] = $card['name'];
		}
		return $json ? json_encode($names) : $names; 
	}
	
}