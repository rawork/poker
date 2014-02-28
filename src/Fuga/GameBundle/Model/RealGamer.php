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

	public $position = 0;
	public $question;
	public $combination;

	private $doc;
	private $container;
	private $cookietime = 604800;
	
	private $timers     = array(
		'answer'     => array('handler' => 'onNoAnswer', 'holder' => 'answer-timer', 'time' => 14),
		'bet'        => array('handler' => 'onFold', 'holder' => 'game-timer', 'time' => 46),
		'change'     => array('handler' => 'onClickNoChange', 'holder' => 'change-timer', 'time' => 31),
	);
	
	public function __construct(Gamer $doc, Container $container) {
		$this->container    = $container;
		$this->doc = $doc;
		
		if ($this->isState(0)) {
			$this->doc->setState(1);
			$this->save();
		}
		
		$question = $this->doc->getQuestion();
		$combination = $this->doc->getCombination();
		
		$this->question = array_shift($question);
		$this->combination = array_shift($combination);
		$this->haveBuyQuestion();
		
		setcookie('gamerstate', $this->doc->getState(), time() + $this->cookietime, '/');
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
	
	public function setRank($value){
		$this->doc->setRank($value);
	}

	public function getQues(){
		return $this->doc->getQues();
	}

	public function setQues($value){
		$this->doc->setQues($value);
	}
	
	public function setTimes($value){
		$this->doc->setTimes($value);
	}
	
	public function setUpdated($value){
		return $this->doc->setUpdated($value);
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
	
	public function getBank() {
		return $this->doc->getBank();
	}
	
	public function setBank($value) {
		$this->doc->setBank($value);
	}
	
	public function getBet2(){
		return $this->doc->getBet2();
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
//		if (count($denied) > 0) {
//			return implode(',', $denied);
//		} else {
//			return '0';
//		}

		return $denied ?: array(0);
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
			return 0;
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
		$this->doc->setBet2( $this->doc->getBet2() + $bet );
		$this->doc->setMove('bet');
		$this->save();
		
		return $bet;
	}
	
	public function check($maxbet) {
		$bet = $maxbet - $this->doc->getBet();
		if ($bet < 0) {
			throw new GameException('Неправильная ставка');
		}
		if ($this->doc->getChips() <= 0) {
			return 0;
		}
		
		if ( $bet >= $this->doc->getChips() ) {
			$this->doc->setAllin(true);
			$bet = $this->doc->getChips();
		}
		
		$this->doc->setChips( $this->doc->getChips() - $bet );
		$this->doc->setBet( $this->doc->getBet() + $bet );
		$this->doc->setBet2( $this->doc->getBet2() + $bet );
		$this->doc->setMove('check');
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
		$this->removeTimer();
		$this->save();
//		$questions = $this->container->getItems(
//				'game_poll',
//				'id < 141 AND id NOT IN('.$this->getDeniedQuestions().')'
//		);
//		$question = $questions[array_rand($questions)];
		$questiondoc = $this->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Question')
				->field('question')->notIn($this->getDeniedQuestions())
				->limit(1)
				->skip(rand(1,20))
				->getQuery()->getSingleResult();
		$question = array();
		if ($questiondoc) {
			$question = array(
				'id'      => $questiondoc->getQuestion(),
				'name'    => $questiondoc->getName(),
				'answer1' => $questiondoc->getAnswer1(),
				'answer2' => $questiondoc->getAnswer2(),
				'answer3' => $questiondoc->getAnswer3(),
				'answer4' => $questiondoc->getAnswer4(),
				'answer'  => $questiondoc->getAnswer(),
			);
		}
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
			$this->save();
		}
	}
	
	public function nochangeCard() {
		$this->doc->setTimes(0);
		if ($this->checkActive()) {
			$this->checkCombination();
		}
		$this->save();
	}
	
	public function foldCards() {
		$this->doc->setTimer(array());
		$this->doc->setCards(array());
		$this->doc->setFold(true);
		$this->doc->setCombination(array());
		$this->doc->setRank(null);
		$this->save();
	}
	
	public function answerQuestion($answerNo, Game $game) {
		$this->removeTimer();
		if ($this->doc->getTimes() > 0 && !$this->question) {
			return 0;
		}
		if ($this->doc->getTimes() <= 0) {
			$this->doc->setQuestion(array());
			$this->doc->setTimes(0);
			$this->save();
			return 0;
		}
		$this->doc->setTimes($this->doc->getTimes()-1);
		if ($answerNo == $this->question['answer']) {
			$cardNo = $this->doc->getCard();
			$myCards = $this->doc->getCards();
			$myCards[$cardNo] = array_shift($game->getAsyncCards(1, $this->getId()));
			$this->doc->setCards($myCards);
			$this->doc->setQues($this->doc->getQues() + 1);
		} else {
			$this->giveChips(-1);
		}
		$this->question = null;
		$this->doc->setCard(-1);
		$this->doc->setQuestion(array());
		if ($this->getChips() > 0) {
			$this->checkCombination();
		} else {
			$this->doc->setFold(true);
			$this->doc->setCards(array());
			$this->doc->setTimes(0);
		}
		if ($this->doc->getTimes() > 0) {
			$this->setTimer('change');
			$this->startTimer();
		}
		$this->save();
		
		return $this->doc->getTimes();
	}
	
	public function answerBuyQuestion($answerNo, Game $game) {
		if (!$this->question) {
			throw new GameException('Потерялся вопрос');
		}
		if ($answerNo == $this->question['answer']) {
			$this->doc->setQues($this->doc->getQues() + 1);
			$this->doc->setChips($this->doc->getChips() + $game->minbet);
			$this->doc->setActive($this->doc->getChips() > 0); // TODO ???$game->minbet 
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
		$cards = $combination->get($this->doc->getCards(), $flop);
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
		}
	}
	
	public function startTimer() {
		$timer = array_shift($this->doc->getTimer());
		if (is_array($timer) && isset($timer['handler'])) {
			setcookie('timerholder', $timer['holder'], time()+$this->cookietime, '/');
			setcookie('timerhandler', $timer['handler'], time()+$this->cookietime, '/');
			setcookie('timerstop',  $timer['time'], time()+$this->cookietime, '/');
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
	}
	
	public function save() {
		return $this->container->get('odm')->flush();
	}
	
	public function clear() {
		$this->container->get('odm')->remove($this->doc);
	}
}
