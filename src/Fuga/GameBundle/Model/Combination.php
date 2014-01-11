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
	
	private function isStreet(array $suite) {
		if (count($suite) < 4) {
			return false;
		}
		
		$cards = array(
			'rank'   => self::STREET_FLASH,
			'weight' => 0,
			'cards'  => array(),
		);
		$hasJoker = $this->hasJoker;
		$weights = array();
		$newSuite = array();
		
		if ($suite[0]['weight'] == $this->jokerWeight) {
			array_shift($suite);
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
		$i = 0;
		
		foreach ($newSuite as $card) {
			$quantity = count($cards['cards']);
			if ($quantity == 0) {
				$cards['weight'] += $card['weight'];
				$cards['cards'][] = $card;
				continue;
			}

			if (($cards['cards'][$quantity-1]['weight'] >> 1) === $card['weight']) {
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
				$cards['weight'] = $card['weight'];
				$cards['cards'] = array($card);
				$i = 1;
			}
			
			if ($quantity == 5) {
				break;
			}
		}
		
		if (count($cards['cards']) == 4 && $cards['cards'][0]['weight'] == $this->fiveWeight && $aceCard) {
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
		return 4 == count($suite) || (3 == count($suite) && $this->hasJoker);
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
	
	private function isFullHouse(array $triples, array $pairs) {
		if (!$triples && !$pairs) {
			return false;
		}
		$hasJoker = $this->hasJoker;
		if ($hasJoker) {
			$triples = array_merge($triples, $pairs);
			if (count($triples) < 2) {
				return false;
			} else {
				$triple = $this->highSuite($triples);
				return true;
			}
		} else {
			$triple = $this->highSuite($triples);
			$pair   = $this->highSuite($pairs);
			if ($triple && $pair) {
				return true;
			}
		}

		return false;
	}
	
	private function isOneTriple(array $triples, array $pairs) {
		if (!$triples && !$pairs) {
			return false;
		}
		$hasJoker = $this->hasJoker;
		if ($hasJoker) {
			$triples = array_merge($triples, $pairs);
			if (count($triples) == 0) {
				return false;
			} else {
				$triple = $this->highSuite($triples);
				return true;
			}
		} else {
			return false;
		}

		return false;
	}
	
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
	
	private function calculateRank() {
		$args = func_get_args();
		$sum = 0;
		foreach ($suite as $card) {
			$sum += $card['weight'];
		}
		
		return $sum;
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
	
	// TODO implementation highCard
	private function highCard(array $suites) {
		$highCard = false;
		foreach ($suites as $suite) {
			foreach ($suite as $card) {
				if ($card['weight'] > $highCard['weight']) {
					$highCard = $card;
				}
			}	
		}
		
		return $highCard;
	}
	
	// TODO implementation getKiker
	private function getKiker($suite, $rank) {
		
	}

	public function get($suite) {
		$cards = array();
		usort($suite, array('self', 'sortByWeight'));
		$this->hasJoker = $this->hasJoker($suite);
		$singles = array();
		$pairs   = array();
		$triples = array();
		
		$sameSuite = $this->sameSuit($suite);
		foreach ($sameSuite as $suit) {
			if ($cardsFlash = $this->isFlash($suit)) {
				if ($cardsStreet = $this->isStreet($suite)) {
					if ($cardsRoyal = $this->isRoyal($cardsStreet['cards'])) {
						if ($cardsStreet['weight'] == $cardsRoyal['weight']) {
							return $cardsRoyal;
						}
					}
					
					if ($cardsStreet['weight'] == $cardsFlash['weight']) {
						return $cardsStreet;
					}
					
				}
				
				return $cardsFlash;
			}
		}
		
		$sameWeight = $this->sameWeight($suite);
		
		foreach ($sameWeight as $suit) {
			if ($this->isFour($suit)) {
//				if ($this->hasJoker) {
//					return $this->rankName(self::POKER);
//				}
				return $this->rankName(self::FOUR); 
			} elseif ($this->isTriple($suit)) {
				$triples[] = $suit;
			} elseif ($this->isPair($suit)) {
				$pairs[] = $suit;
			} elseif ($this->isSingle($suit)) {
				$singles[] = $suit;
			}
		}
		if ($this->isFullHouse($triples, $pairs)) {
			return $this->rankName(self::FULL_HOUSE);
		} elseif ($this->isStreet($suite)) {
			return $this->rankName(self::STREET);
		} elseif ($this->isOneTriple($triples, $pairs)) {
			return $this->rankName(self::TRIPLE);
		} elseif ($this->isPairs($pairs, $singles, 2)) {
			return $this->rankName(self::TWO_PAIRS);
		} elseif ($this->isPairs($pairs, $singles, 1)) {
			return $this->rankName(self::PAIR);
		} else {
			rsort($singles);
			$singles = array_slice($singles, 0, 5);
			return $this->rankName(self::HIGH_CARD);
		}
		
		return $cards ?: false;
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