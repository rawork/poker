<?php

namespace Fuga\GameBundle\Model;

class Combination {
	
	const HIGH_CARD		= 0;
	const PAIR			= 1;
	const TWO_PAIRS		= 2;
	const TRIPLE		= 3;
	const STREET		= 4;
	const FLASH			= 5;
	const FULL_HOUSE	= 6;
	const FOUR			= 7;
	const STREET_FLASH  = 8;
	const FLASH_ROYAL   = 9;
	const POKER			= 10;
	
	private $jokerWeight = 8192;
	private $aceWeight   = 4096;
	private $fiveWeight  = 8;
	private $hasJoker;
	private $weights = array(
		1    => '2', 
		2    => '3', 
		4    => '4', 
		8    => '5', 
		16   => '6', 
		32   => '7', 
		64   => '8', 
		128  => '9', 
		256  => '10', 
		512  => 'jack', 
		1024 => 'queen', 
		2048 => 'king', 
		4096 => 'ace'
	);
	private $suits = array(
		1 => 'diams', 
		2 => 'hearts', 
		4 => 'spades', 
		8 => 'clubs'
	);
	private $ranks = array(
		0  => 'Старшая карта',
		1  => 'Пара',
		2  => '2 пары',
		3  => 'Тройка',
		4  => 'Стрит',
		5  => 'Флеш',
		6  => 'Фулл-хаус',
		7  => 'Каре',
		8  => 'Стрит-флеш',
		9  => 'Флеш-рояль',
		10 => 'Покер'
	);
	
	static public function sortByWeight($a, $b) {
        if ($a['weight'] == $b['weight']) {
            return 0;
        }
        return ($a['weight'] < $b['weight']) ? +1 : -1;
	}
	
	public function rankName($id) {
		return $this->ranks[$id];
	}
	
	private function sameSuit(array $suite) {
		$array = array();
		foreach ($suite as $card) {
			foreach ($this->suits as $suit => $name) {
				if ($card['suit'] == $suit) {
					if ( empty($array[$suit]) ) {
						$array[$suit] = array();
					}
					$array[$suit][$card['weight']] = $card;
				}

			}
		}
		
		return $array;
	}
	
	private function sameWeight(array $suite) {
		$array = array();
		foreach ($suite as $card) {
			foreach ($this->weights as $weight => $name) {
				if ($card['weight'] == $weight) {
					if ( empty($array[$weight]) ) {
						$array[$weight] = array();
					}
					$array[$weight][$card['suit']] = $card;
					break;
				}
			}
		}
		
		return $array;
	}
	
	private function isRoyal(array $suite) {
		$cards = array(
			'rank'   => self::FLASH_ROYAL,
			'weight' => 0,
			'cards'  => array(),
		);
		
		foreach ($suite as $card) {
			$cards['weight'] += $card['weight']; 
			$cards['cards'][] = $card;
		}
		
		return 7936 == $cards['weight'] ? $cards : false;
	}
	
	// TODO Доработать
	private function isStreet(array $suite) {
		if (count($suite) < 4) {
			return false;
		}
		
		$cards = array(
			'rank'   => self::STREET,
			'weight' => 0,
			'cards'  => array(),
		);
		$hasJoker = $this->hasJoker;
		$weights = array();
		$newSuite = array();
		$firstCard = array_shift($suite);
		
		if ($firstCard['weight'] < $this->jokerWeight) {
			array_unshift($suite, $firstCard);
		}
		
		foreach($suite as $card) {
			if (isset($weights[$card['weight']])) {
				continue;
			}
			$weights[$card['weight']] = true;
			$newSuite[] = $card;
		}
		unset($weights);
		$aceCard = $newSuite[0]['weight'] == $this->aceWeight ? $newSuite[0] : false;

		foreach ($newSuite as $card) {
			$quantity = count($cards['cards']);
			if ($quantity == 0) {
				if ($hasJoker && ($card['weight'] << 1 <= $this->aceWeight)) {
					$cards['weight'] += $card['weight'] << 1;
					$cards['cards'][] = array(
						'name' => $this->weights[$card['weight'] << 1].'_'.$this->suits[$card['suit']],
						'suit' => $card['suit'],
						'weight' => $card['weight'] << 1
					);
					$hasJoker = false;
				}
				$cards['weight'] += $card['weight'];
				$cards['cards'][] = $card;
				
				continue;
			}
			
			if (($cards['cards'][$quantity-1]['weight'] >> 1) == $card['weight']) {
				$cards['weight'] += $card['weight'];
				$cards['cards'][] = $card;
			} elseif ($hasJoker && ($cards['cards'][$quantity-1]['weight'] >> 2) === $card['weight']) {
				$cards['weight'] += $cards['cards'][$quantity-1]['weight'] >> 1;
				$cards['cards'][] = array(
					'name' => $this->weights[$cards['cards'][$quantity-1]['weight'] >> 1].'_'.$this->suits[$cards['cards'][$quantity-1]['suit']],
					'suit' => $cards['cards'][$quantity-1]['suit'],
					'weight' => $cards['cards'][$quantity-1]['weight'] >> 1
				);
				$cards['weight'] += $card['weight'];
				$cards['cards'][] = $card;
				$hasJoker = false;
			} else {
				$jokerCard = array_shift($cards['cards']);
				$cards['weight'] -= $jokerCard['weight'];
				$hasJoker = $this->hasJoker;
				if ($hasJoker && ($card['weight'] << 1 <= $this->aceWeight)) {
					$cards['weight'] += $card['weight'] << 1;
					$cards['cards'][] = array(
						'name' => $this->weights[$card['weight'] << 1].'_'.$this->suits[$card['suit']],
						'suit' => $card['suit'],
						'weight' => $card['weight'] << 1
					);
					$hasJoker = false;
					$cards['weight'] += $card['weight'];
					$cards['cards'][] = $card;
				} else {
					$cards['weight'] = $card['weight'];
					$cards['cards'] = array($card);
				}
				
			}
			
			if (count($cards['cards']) == 5) {
				break;
			}
		}
		
		if ($cards['cards'][0]['weight'] == $this->fiveWeight && count($cards['cards']) == 4 && $aceCard) {
			$cards['cards'][] = $aceCard;
		}
		
		if (count($cards['cards']) < 5) {
			return false;
		}
		
		return $cards;
	}
	
	private function isFlash(array $suite) {
		if (count($suite) < 4) {
			return false;
		}
		
		$cards = array(
			'rank'   => self::FLASH,
			'weight' => 0,
			'cards'  => array(),
		);
		$hasJoker = $this->hasJoker;
		$highCard = $this->aceWeight;
		
		foreach ($suite as $card) {
			if ($hasJoker) {
				if ($card['weight'] < $highCard) {
					$hasJoker = false;
					$cards['weight'] += $highCard;
					$cards['cards'][] = array(
						'name' => $this->weights[$highCard].'_'.$this->suits[$card['suit']], 
						'suit' => $card['suit'], 
						'weight' => $highCard
					);
				} else {
					$highCard = $highCard >> 1;
				}
			}
			$cards['weight'] += $card['weight'];
			$cards['cards'][] = $card;
			if (count($cards['cards']) == 5) {
				break;
			}
		}
		
		if (count($cards['cards']) < 5) {
			return false;
		}
		
		return $cards;
	}
	
	private function isFour(array $suite) {
		if (count($suite) < 3) {
			return false;
		}
		
		$cards = array(
			'rank' => self::FOUR,
			'weight' => 0,
			'cards' => array() 
		);
		$suits = array();
		
		foreach ($suite as $card) {
			$suits[] = $card['suit'];
			$cards['weight'] += $card['weight'];
			$cards['cards'][] = $card;
		}
		
		$suit = array_shift(array_diff(array_keys($this->suits), $suits));
		
		if ($this->hasJoker && count($suit)) {
			$cards['weight'] += $cards['cards'][0]['weight'];
			$cards['cards'][] = array(
				'name' => $this->weights[$cards['cards'][0]['weight']].'_'.$this->suits[$suit],
				'suit' => $suit,
				'weight' => $cards['cards'][0]['weight'],
			);
		}
		
		if (count($cards['cards']) < 4) {
			return false;
		}
		
		return $cards;
	}
	
	private function isFullHouse(array $triples, array $pairs) {
		if (!$triples && !$pairs) {
			return false;
		}
		
		$cards = array(
			'rank' => self::FULL_HOUSE,
			'weight' => 0,
			'cards' => array(),
		);
		$hasJoker = $this->hasJoker;
		$triple = null;
		$pair = null;
		
		if (count($triples) > 1) {
			$triple = array_shift($triples);
			$pair   = array_slice(array_shift($triples), 0, 2);
		} elseif (count($triples) == 1 && count($pairs) > 0) {
			$triple = array_shift($triples);
			$pair   = array_shift($pairs);
		} elseif (count($pairs) > 1 && $hasJoker) {
			$triple = array_shift($pairs);
			$pair   = array_shift($pairs);
			$suit = array_shift(array_diff(array_keys($this->suits), array_keys($triple)));
			$firstCard = array_shift($triple);
			$triple[$suit] = array(
				'name' => $this->weights[$firstCard['weight']].'_'.$this->suits[$suit],
				'suit' => $suit,
				'weight' => $firstCard['weight'],
			);
			array_unshift($triple, $firstCard);
			$hasJoker = false;
		}
		
		if (!$triple || !$pair) {
			return false;
		}
		
		$newSuite = array_merge($triple, $pair);
		foreach ($newSuite as $card) {
			$cards['weight'] += $card['weight'];
			$cards['cards'][] = $card;
		}  
		
		if (count($cards['cards']) < 5) {
			return false;
		}

		return $cards;
	}
	
	
	// TODO New implementation
	private function isOneTriple(array $triples, array $pairs) {
		if (!$triples && !$pairs) {
			return false;
		}
		
		$cards = array(
			'rank' => self::TRIPLE,
			'weight' => 0,
			'cards' => array(),
		);
		$hasJoker = $this->hasJoker;
		$triple = null;
		
		if (count($triples) > 0) {
			$triple = array_shift($triples);
		} elseif (count($pairs) > 0 && $hasJoker) {
			$triple = array_shift($pairs);
			$suit = array_shift(array_diff(array_keys($this->suits), array_keys($triple)));
			$firstCard = array_shift($triple);
			$triple[$suit] = array(
				'name' => $this->weights[$firstCard['weight']].'_'.$this->suits[$suit],
				'suit' => $suit,
				'weight' => $firstCard['weight'],
			);
			array_unshift($triple, $firstCard);
			$hasJoker = false;
		}
		
		var_dump($triple);
		
		if (!$triple) {
			return false;
		}
		
		foreach ($triple as $card) {
			$cards['weight'] += $card['weight'];
			$cards['cards'][] = $card;
		}
		
		var_dump($cards);

		return $cards;
	}
	
	// TODO New implementation
	private function isPairs(array $pairs, array $singles, $quantity = 1) {
		$hasJoker = $this->hasJoker;
		if ($hasJoker) {
			$single = $this->highCard($singles);
			$pairs[] = array($single);
			if (count($pairs) < $quantity) {
				return false;
			} else {
				$pair = $this->highSuite($pairs);
				return true;
			}
		} elseif (count($pairs) >= $quantity) {
			$pair = $this->highSuite($pairs);
			return true; 
		}

		return false;
	}
	
	private function highCard(array $suite) {
		$cards = array(
			'rank' => self::HIGH_CARD,
			'weight' => 0,
			'cards' => array(),
		);
		
		foreach ($suite as $card) {
			$cards['weight'] += $card['weight'];
			$cards['cards'][] = $card;
		}
		
		return $cards;
	}
	
	private function isTriple(array $suite) {
		return 3 == count($suite);
	}
	
	private function isPair(array $suite) {
		return 2 == count($suite);
	}
	
	private function isSingle(array $suite) {
		return 1 == count($suite);
	}
	
	private function hasJoker($suite) {
		$hasJoker = false;
		foreach ($suite as $card) {
			if ($this->jokerWeight == $card['weight']) {
				$hasJoker = true;
				break;
			}
		}
		
		return $hasJoker;
	}
	
	private function highSuite(array $suites) {
		$sums = array();
		if (!$suites) {
			return null;
		}
		foreach ($suites as $suite) {
			$sum = 0;
			foreach ($suite as $card) {
				$sum += $card['weight'];
			}
			$sums[] = $sum;
		}
		
		return $suites[array_search(max($sums), $sums)];
	}
	
	public function get($suite) {
		usort($suite, array('self', 'sortByWeight'));
		$this->hasJoker = $this->hasJoker($suite);
		$singles = array();
		$pairs   = array();
		$triples = array();
		
		$sameSuite = $this->sameSuit($suite);
		foreach ($sameSuite as $suit) {
			if ($cardsFlash = $this->isFlash($suit)) {
				if ($cardsStreet = $this->isStreet($suit)) {
					if ($cardsRoyal = $this->isRoyal($cardsStreet['cards'])) {
						return $cardsRoyal;
					}
					$cardsStreet['rank'] = self::STREET_FLASH;
					
					return $cardsStreet;
				}
				
				return $cardsFlash;
			}
		}
		
		$sameWeight = $this->sameWeight($suite);
		
		foreach ($sameWeight as $suit) {
			if ($cards = $this->isFour($suit)) {
				return $cards; 
			} elseif ($this->isTriple($suit)) {
				$triples[] = $suit;
			} elseif ($this->isPair($suit)) {
				$pairs[] = $suit;
			} elseif ($this->isSingle($suit)) {
				$singles[] = $suit;
			}
		}
		
		if ($cards = $this->isFullHouse($triples, $pairs)) {
			return $cards;
		} elseif ($cards = $this->isStreet($suite)) {
			return $cards;
		} elseif ($cards = $this->isOneTriple($triples, $pairs)) {
			return $cards;
		} elseif ($cards = $this->isPairs($pairs, $singles)) {
			return $cards;
		}
		
		return $this->highCard($suite);
	}
	
	public function compare(array $suites) {
		$cards = array();
		$i = 0;
		
		while ($suite = array_shift($suites)) {
			
			if (1 == ++$i) {
				$cards = $suite;
				return;
			}
				
			if ($cards['rank'] < $suite['rank']) 
			{
				$cards = $suite;
				continue;
			} elseif ($cards['rank'] == $suite['rank']) {
				if ($cards['weight'] == $suite['weight']) 
				{
					$cards = $suite;
					continue;
				}
			}
		}
		
		return $cards;
	}
	
}