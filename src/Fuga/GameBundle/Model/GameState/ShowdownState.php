<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\RealGamer;

class ShowdownState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function distributeWin($gamer) {
		try {
			if (!$this->game->lock($gamer->getId())) {
				return $this->game->getStateNo();
			}

			$this->game->removeTimer();
			
			$winners = $this->game->getWinner();
			$allins  = $this->game->getWinnera();
			$bank = $this->game->takeBank();
			
			if (count($allins) > 0) {
				if (count($winners) == 0) {
					$maxallinbank = $bank; 
				} else {
					$maxallinbank = 0;
					foreach ($allins as $winner) {
						if ($winner['bank'] > $maxallinbank) {
							$maxallinbank = $winner['bank'];
						}
					}
				}
				
				$numWin = count($allins);
				if ($numWin > 1) {
					$nextBank = $maxallinbank % $numWin;
					$share = ($maxallinbank - $nextBank) / $numWin;
					$this->game->setBank($this->game->getBank() + $nextBank);
				} else {
					$share = $maxallinbank;
				}

				foreach ($allins as &$winner) {
					$winner['win'] = $share;
				}
				unset($winner);

				$bank -= $maxallinbank; 
			}
			
			if (count($winners) > 0) {
				$numWin = count($winners);
				if ($numWin > 1) {
					$nextBank = $bank % $numWin;
					$share = ($bank - $nextBank) / $numWin;
					$this->game->setBank($this->game->getBank() + $nextBank);
				} else {
					$share = $bank;
				}
				foreach ($winners as &$winner) {
					$winner['win'] = $share;
				}
				unset($winner);
			}

			$winners = array_merge($winners, $allins);

			$gamers = $this->game->container->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->field('board')->equals($this->game->getId())
					->field('active')->equals(true)
					->getQuery()->execute();

			$ids = array();

			foreach ($gamers as $doc) {
				foreach ($winners as $winner) {
					if ($doc->getUser() == $winner['user']) {
						$doc->setChips( $doc->getChips() + $winner['win'] );
						foreach ($winner['cards'] as $card) {
							if ($card['name'] == 'joker' && !in_array($winner['user'], $ids)) {
								$ids[] = $winner['user'];
								$doc->setChips( $doc->getChips() + 2 );
							}
						}
					}
				}

				if (time() > $this->game->stopbuytime) {
					$doc->setActive($doc->getChips() >= $this->game->minbet);
					if (!$doc->getActive()) {
						$this->game->acceptBet($doc->getChips());
						$this->game->confirmBets();
						$doc->setChips(0);
					}
				}	
				$doc->setBet(0);
				$doc->setBet2(0);
				$doc->setBank(0);
				$doc->setMove('nomove');
				$doc->setCards(array());
				$doc->setRank('');
				$doc->setCombination(array());
				$doc->setWinner(false);
				$doc->setFold(false);
//				$query = '1=1';
//				if ($denied = $doc->getDenied()) {
//					$query = 'id < 141 AND id NOT IN('.implode(',', $denied).')';
//				}
//				$questions = $this->game->container->getItems('game_poll', $query);
//				shuffle($questions);
//				$buy = array_slice($questions, 0, 3);
//				foreach ($buy as $question) {
//					$denied[] = $question['id'];
//				}

				$buy = array();
				$denied = $doc->getDenied() ?: array(0);
				$questions = $this->game->container->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Question')
					->field('question')->notIn($denied)
					->limit(3)
					->skip(rand(1,2))
					->getQuery()->execute();

				foreach ($questions as $questiondoc) {
					$buy[] = array(
						'id'      => $questiondoc->getQuestion(),
						'name'    => $questiondoc->getName(),
						'answer1' => $questiondoc->getAnswer1(),
						'answer2' => $questiondoc->getAnswer2(),
						'answer3' => $questiondoc->getAnswer3(),
						'answer4' => $questiondoc->getAnswer4(),
						'answer'  => $questiondoc->getAnswer(),
					);
					$denied[] = $questiondoc->getQuestion();
				}

				$doc->setBuy($buy);
				$doc->setDenied($denied);
			}

			$this->game->emptyWinner();
			$this->game->setFlop(array());
			$this->game->setBets(0);
			$this->game->setMaxbet(0);
			$this->game->save();

			if (!$this->game->existsJoker()) {
				$this->game->setTimer('prebuy');
				$this->game->startTimer();
				$this->game->setState(AbstractState::STATE_JOKER);
			} elseif (time() > $this->game->stopbuytime) {
				if (!$this->game->existsGamers()) {
					$this->game->removeTimer();
					$this->game->stopTime();
					$this->game->setState(self::STATE_END);
				} else {
					$this->game->setTimer('next');
					$this->game->startTimer();
					$this->game->setState(self::STATE_ROUND_END);
				}
			} else {
				$this->game->setTimer('buy');
				$this->game->startTimer();
				$this->game->setState(AbstractState::STATE_BUY);
			}
			
			$this->game->setUpdated(time());
			$this->game->save();
			$this->game->unlock($gamer->getId());
		} catch (\Exception $e) {
			$this->game->unlock($gamer->getId());
		}
		
		return $this->game->getStateNo();
	}
	
//	public function sync() {
//		$gamer = null;
//		$gamerdoc = $this->game->container->get('odm')
//				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
//				->field('board')->equals($this->game->getId())
//				->field('state')->gt(0)
//				->getQuery()->getSingleResult();
//		$timer = $this->game->getTimer();
//		$timer = array_shift($timer);
//		if ($timer && intval($timer['time'])+5 < time()) {
//			$this->game->container->get('log')->addError(
//					'game'.$this->game->getId()
//					.' :showdown.find.outtimer '
//					.(intval($timer['time']) - time())
//			);
//			$this->game->container->get('odm')
//				->createQueryBuilder('\Fuga\GameBundle\Document\Board')
//				->findAndUpdate()
//				->field('board')->equals($this->game->getId())
//				->field('gamer')->set(0)
//				->getQuery()->execute();
//			$gamer = new RealGamer($gamerdoc, $this->game->container);
//		}
//
//		if ($gamer) {
//			$this->game->distribute($gamer);
//		}
//	}
	
}


