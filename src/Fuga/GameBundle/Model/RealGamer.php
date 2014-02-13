<?php

namespace Fuga\GameBundle\Model;

use Fuga\Component\Container;
use Fuga\GameBundle\Model\Game;
use Fuga\GameBundle\Document\Gamer;
use Fuga\GameBundle\Model\Exception\GameException;

class RealGamer {
	
	const STATE_NO    = 0;
	const STATE_READY = 1;
	const STATE_OUT   = 3;
	const STATE_END   = 4;

	public $position = 0;
	public $question;
	public $combination;

	private $doc;
	private $container;
	private $cookietime = 7776000;
	
	private $timers     = array(
		'answer'     => array('handler' => 'onNoAnswer', 'holder' => 'answer-timer', 'time' => 14),
		'bet'        => array('handler' => 'onFold', 'holder' => 'game-timer', 'time' => 601),
	);
	
	public function __construct($userId, Container $container) {
		$this->container    = $container;
		$this->doc = $this->container->get('odm')
				->getRepository('\Fuga\GameBundle\Document\Gamer')
				->findOneByUser(intval($userId));
		
		if (!$this->doc) {
			$gamer = $this->container->getItem('account_member', 'user_id='.$userId);
			if (!$gamer) {
				throw new Exception\GameException('Ошибка создания игрока');
			}
			$this->doc = new Gamer();
			$this->doc->setMember($gamer['id']);
			$this->doc->setUser($userId);
			$this->doc->setBoard($gamer['board_id']);
			$this->doc->setName($gamer['name']);
			$this->doc->setLastname($gamer['lastname']);
			$this->doc->setSeat($gamer['seat']);
			$this->doc->setChips($gamer['chips']);
			$this->doc->setAvatar(isset($gamer['avatar_value']['extra']) 
				? $gamer['avatar_value']['extra']['main']['path'] 
				: '/bundles/public/img/avatar_empty.png');
			$this->container->get('odm')->persist($this->doc);
			$this->save();
		}
		
		if ($this->isState(0)) {
			$this->doc->setState(1);
			$this->save();
		}
		
		$this->question = array_shift($this->doc->getQuestion());
		$this->combination = array_shift($this->doc->getCombination());
		$this->haveBuyQuestion();
		
		setcookie('gamerstate', $this->doc->getState(), time()+7000000, '/');
	}
	
	public function getId() {
		return $this->doc->getUser();
	}
	
	public function getName(){
		return $this->doc->getName();
	}
	
	public function getBoard(){
		return $this->doc->getBoard();
	}
	
	public function getRank(){
		return $this->doc->getRank();
	}
	
	public function getSeat(){
		return $this->doc->getSeat();
	}
	
	public function getLastname(){
		return $this->doc->getLastname();
	}
	
	public function getChips(){
		return $this->doc->getChips();
	}
	
	public function getAllin(){
		return $this->doc->getAllin();
	}
	
	public function getBet(){
		return $this->doc->getBet();
	}
	
	public function getBuy(){
		return $this->doc->getBuy();
	}
	
	public function getCards(){
		return $this->doc->getCards();
	}
	
	public function getAvatar(){
		return $this->doc->getAvatar();
	}
	
	public function getQuestion(){
		return $this->doc->getQuestion();
	}
	
	public function getState(){
		return $this->doc->getState();
	}
	
	public function getDeniedQuestions() {
		$denied = $this->doc->getDenied();
		if (count($denied) > 0) {
			return implode(',', $denied);
		} else {
			return '0'; 
		}
	}
	
	public function addDeniedQuestion($id) {
		$denied = $this->doc->getDenied();
		$denied[] = $id;
		$this->doc->setDenied($denied);
	}
	
	public function getRivalPosition($rivalSeat, $numOfGamers = 6) {
		$seat = $this->doc->getSeat();
		$rivalSeat = intval($rivalSeat);
		if ($rivalSeat == 0) {
			throw new Exception\GameException('За столом игрок без места');
		}
		if ($rivalSeat == $seat) {
			return 0;
		}
		switch($numOfGamers){
			case 1:
				throw new Exception\GameException('За столом только один игрок');
			case 2:
				return 3;
			case 3:
				$leftOffset = 1;
				$rightOffset = 2;
				break;
			case 4:
				$leftOffset = 1;
				$rightOffset = 1;
				break;
			case 5:
				$leftOffset = 0;
				$rightOffset = 1;
				break;
			default:
				$leftOffset = 0;
				$rightOffset = 0;
				break;
		}
		if ($rivalSeat > $seat) {
			$position = $rivalSeat - $seat + $leftOffset;
		} else {
			$position = 6 - ($seat - $rivalSeat + $rightOffset);
		}
		return $position;
	}
	
	public function isCombination($cardName) {
		$combination = $this->doc->getCombination();
		
		if (is_array($combination)) {
			return in_array($cardName, $combination);
		}
		
		return false;
	}
	
	public function isWinner() {
		return $this->doc->getWinner();
	}
	
	public function getTimes() {
		return $this->doc->getTimes();
	}
	
	public function bet($bet, $maxbet) {
		if ($this->doc->getChips() <= 0) {
			$this->doc->setChips(0);
			$this->save();
			throw new GameException('Нет фишек');
		}
		if ( $maxbet > $this->doc->getChips() ) {
			$this->doc->setAllin(true);
			$bet = $this->doc->getChips();
		} elseif ($bet >= $this->doc->getChips()) {
			$this->doc->setAllin(true);
			$bet = $this->doc->getChips();
		}
		$this->doc->setChips( $this->doc->getChips() - $bet );
		$this->doc->setBet( $this->doc->getBet() + $bet );
		$this->save();
		
		return $bet;
	}
	
	public function check($maxbet) {
		$bet = $maxbet - $this->doc->getBet();
		if ($bet < 0) {
			throw new GameException('Неправильная ставка');
		}
		if ($this->doc->getChips() <= 0) {
			$this->doc->setChips(0);
			$this->save();
			throw new GameException('Нет фишек');
		}
		
		if ( $bet >= $this->doc->getChips() ) {
			$this->doc->setAllin(true);
			$bet = $this->doc->getChips();
		}
		
		$this->doc->setChips( $this->doc->getChips() - $bet );
		$this->doc->setBet( $this->doc->getBet() + $bet );
		$this->save();
		
		return $bet;
	}
	
	public function emptyBet() {
		$this->doc->setBet(0);
	}
	
	public function giveChips($chips) {
		$this->doc->setChips($this->doc->getChips() + $chips);
	}
		
	public function isActive() {
		return $this->doc->getActive();
	}
	
	public function isHere() {
		return $this->getActive() && $this->getState() == 1;
	}
	
	public function isState($state) {
		return $state == $this->doc->getState();
	}
	
	public function checkActive() {
		 $this->doc->setActive($this->doc->getChips() > 0);
		 return $this->doc->getActive();
	}
	
	public function changeCard($card) {
		$questions = $this->container->getItems(
				'game_poll', 
				'id NOT IN('.$this->getDeniedQuestions().')'
		);
		$question = $questions[array_rand($questions)];
		$this->question = $question;
		$this->doc->setCard($card);
		$this->doc->setQuestion(array($question));
		$this->addDeniedQuestion($question['id']);
		$this->setTimer('answer');
		$this->startTimer();
		$this->save();
	}
	
	public function haveBuyQuestion() {
		setcookie('gamebuy', $this->doc->getBuy() ? 1 : 0, time() + $this->cookietime, '/');
		return count($this->doc->getBuy()) > 0;
	}
	
	public function buyChips() {
		$buy = $this->doc->getBuy();
		if (is_array($buy) && count($buy) > 0) {
			$question = array_shift($buy);
			$question['number'] = 3 - count($buy);
			$this->question = $question;
			$this->doc->setQuestion(array($question));
			$this->doc->setBuy($buy);
		}
		$this->save();
	}
	
	public function nochangeCard() {
		$this->doc->setTimes(0);
		if ($this->checkActive()) {
			$this->checkCombination();
		}
		$this->save();
	}
	
	public function foldCards() {
		$this->doc->setCards(array());
		$this->doc->setFold(true);
		$this->doc->setCombination(array());
		$this->doc->setRank(null);
		$this->save();
	}
	
	public function answerQuestion($answerNo, Game $game) {
		$this->removeTimer();
		if ($this->doc->getTimes() > 0 && !$this->question) {
			throw new GameException('Потерялся вопрос');
		}
		if ($this->doc->getTimes() <= 0) {
			$this->doc->setQuestion(array());
			$this->doc->setTimes(0);
			$this->save();
			return 0;
		}
		$this->doc->setTimes($this->doc->getTimes()-1);
		if ($answerNo == $this->question['answer']) {
			$isChanged = false;
			while (!$isChanged) {
				if (!$game->lock($this->getId())) {
					usleep(100000);
					continue;
				}
				$cardNo = $this->doc->getCard();
				$myCards = $this->doc->getCards();
				$myCards[$cardNo] = array_shift($game->getCards(1));
				$this->doc->setCards($myCards);
				$game->save();
				$game->unlock($this->getId());
				$isChanged = true;
			}
		} else {
			$this->giveChips(-1);
		}
		$this->question = null;
		$this->doc->setCard(-1);
		$this->doc->setQuestion(array());
		if ($this->checkActive()) {
			$this->checkCombination();
		}
		$this->save();
		
		return $this->doc->getTimes();
	}
	
	public function answerBuyQuestion($answerNo, Game $game) {
		if (!$this->question) {
			throw new GameException('Потерялся вопрос');
		}
		if ($answerNo == $this->question['answer']) {
			$this->doc->setChips($this->doc->getChips() + $game->minbet);
		}
		$buy = $this->doc->getBuy();
		if (is_array($buy) && count($buy) > 0) {
			$question = array_shift($buy);
			$question['number'] = 3 - count($buy);
			$this->question = $question;
			$this->doc->setQuestion(array($question));
			$this->doc->setBuy($buy);
		} else {
			$this->question = null;
			$this->doc->setQuestion(array());
		}
		$this->save();
		
		return $this->haveBuyQuestion();
	}
	
	public function checkCombination($flop = array()){
		$combination = new Combination();
		$cards = $combination->get(array_merge($this->doc->getCards(), $flop));
		$combinations = array();
		foreach ($cards['cards'] as $card) {
			$combinations[] = $card['name'];
		}
		$this->doc->setRank($combination->rankName($cards['rank']));
		$this->doc->setCombination($combinations);
		$this->combination = $combinations;
	}
	
	public function setTimer($name) {
		if (array_key_exists($name, $this->timers) && $this->timers[$name]) {
			$timer = $this->timers[$name];
			if ('begin' == $name) {
				$timer['time'] = $this->doc->getFromtime()->getTimestamp();
			} else {
				$timer['time'] = time() + $this->timers[$name]['time'];
			}
			$this->doc->setTimer(array($timer));
			$this->save();
		}
	}
	
	public function startTimer() {
		$timer = array_shift($this->doc->getTimer());
		if (is_array($timer) && isset($timer['handler'])) {
			$date = new \DateTime();
			$date->setTimestamp($timer['time']);
			setcookie('timerholder', $timer['holder'], time()+$this->cookietime, '/');
			setcookie('timerhandler', $timer['handler'], time()+$this->cookietime, '/');
			setcookie('timerstop',  $date->format('c'), time()+$this->cookietime, '/');
		}
		
		return $this;
	}
	
	public function stopTimer() {
		setcookie('timerholder', '', time()+$this->cookietime, '/');
		setcookie('timerhandler', '', time()+$this->cookietime, '/');
		setcookie('timerstop',  0, time()+$this->cookietime, '/');
	}
	
	public function removeTimer() {
		$this->stopTimer();
		$this->doc->setTimer(array());
		$this->save();
	}
	
	public function save() {
		return $this->container->get('odm')->flush();
	}
	
	public function clear() {
		$this->container->get('odm')->remove($this->doc);
		$this->container->get('odm')->flush();
	}
}
