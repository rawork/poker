<?php

namespace Fuga\PokerBundle\Model;

class Calculator {
	
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
	
	private $rank = 0;
	private $joker = 8192;
	private $jokerWeight = 0;
	private $hasJoker;
	private $usedJoker;
	private $weights = array(
		1 => '2', 2 => '3', 4 => '4', 8 => '5', 16 => '6', 32 => '7', 64 => '8', 128 => '9', 
		256 => '10', 512 => 'jack', 1024 => 'queen', 2048 => 'king', 4096 => 'ace'
	);
	private $suits = array(
		1 => 'diams', 
		2 => 'hearts', 
		4 => 'spades', 
		8 => 'clubs'
	);
	private $ranks = array(
		0 => 'Старшая карта',
		1 => 'Пара',
		2 => '2 пары',
		3 => 'Тройка',
		4 => 'Стрит',
		5 => 'Флеш',
		6 => 'Фулл-хаус',
		7 => 'Каре',
		8 => 'Стрит-флеш',
		9 => 'Флеш-рояль',
		10 => 'Покер'
	);
	
	private function rankName($id) {
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
		$sum = 0;
		foreach ($suite as $card) {
			$sum += $card['weight'];
		}
		if ($this->hasJoker){
			$sum += $this->joker;
		}
		
		return $this->hasJoker ? 7936+4096 <= $sum : 7936 <= $sum;
	}
	
	private function isStreet(array $suite) {
		$hasJoker = $this->hasJoker;
		$hasAce   = false;
		$street = array();
		if (count($suite) >= 4) {
			$weights = array();
			foreach($suite as $card) {
				$weights[] = $card['weight'];
			}
			array_unique($weights);
			rsort($weights);
			if ($weights[0] == 8192) {
				array_shift($weights);
			}
			$hasAce =  $weights[0] == 4096;
			foreach ($weights as $weight) {
				if (count($street) == 0 || ($street[count($street)-1] >> 1) === $weight) {
					$street[] = $weight;
					continue;
				} elseif ($hasJoker && ($street[count($street)-1] >> 2) == $weight) {
					$street[] = $street[count($street)-1] >> 1;
					$street[] = $weight;
					$hasJoker = false;
				} elseif (count($street) >= 5 || (count($street) == 4 && $hasJoker) ) {
					break;
				} else {
					$street = array();
					$street[] = $weight;
					$hasJoker = $this->hasJoker;
				}
			}
			while (count($street) < 5) {
				if ($street[count($street)-1] != 1) {
					$hasAce = false;
				}
				if (!$hasJoker && !$hasAce) {
					break;
				}
				if ($street[count($street)-1] == 1 && $hasAce) {
					array_push($street, 0);
					$hasAce = false;
					continue;
				}
				if ($hasJoker) {
					array_unshift($street, $street[0] << 1);
					$hasJoker = false;
					continue;
				}
				
				if ($street[0] == 2048 && $hasJoker) {
					array_unshift($street, 4096);
					
				} 
			}
		}
		
		return count($street) >= 5;
	}
	
	private function isSingle(array $suite) {
		return 1 == count($suite);
	}
	
	private function isPair(array $suite) {
		return 2 == count($suite);
	}
	
	private function isTriple(array $suite) {
		return 3 == count($suite);
	}
	
	private function isFour(array $suite) {
		return 4 == count($suite) || (3 == count($suite) && $this->hasJoker);
	}
	
	private function isFlash(array $suite) {
		return 5 <= count($suite) || (4 == count($suite) && $this->hasJoker);
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
			if ($this->joker == $card['weight']) {
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

	public function checkRank($suite) {
		$singles = array();
		$pairs   = array();
		$triples = array();
		$this->hasJoker = $this->hasJoker($suite);
		$this->usedJoker = false;
		$sameSuite = $this->sameSuit($suite);
		foreach ($sameSuite as $suit) {
			if ($this->isFlash($suit)) {
				if ($this->isStreet($suit)) {
					if ($this->isRoyal($suit)) {
						return $this->rankName(self::FLASH_ROYAL); 
					}
					return $this->rankName(self::STREET_FLASH);
				}
				return $this->rankName(self::FLASH);
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
		
		return false;
	}
	
}