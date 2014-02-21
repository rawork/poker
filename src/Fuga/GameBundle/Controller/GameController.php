<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Fuga\GameBundle\Model\Game;
use Fuga\GameBundle\Model\RealGamer;
use Fuga\GameBundle\Model\Rival;
use Fuga\GameBundle\Model\Deck;
use Fuga\GameBundle\Model\Exception\GameException;
use Fuga\GameBundle\Document\Board;
use Fuga\GameBundle\Document\Gamer;

class GameController extends PublicController {
	
	public function __construct() {
		parent::__construct('game');
	}
	
	public function indexAction() {
		
		$this->get('router')->redirect('/game/game');
		
	}
	
	public function gameAction() {
		$user = $this->get('security')->getCurrentUser();
		
		try {
			if ( strtotime($this->getParam('access_date').' 00:00:01') > time()  ) {
				if (!$user || !$this->get('security')->isGroup('admin')) {
					throw new GameException('Игровой зал открыт<br> только в период 
						проведения игры.<br> Расписание игр 
						размещено<br> в рубрике <a href="/rules">"Правила"</a>.');
				}	
			} elseif ( !$user ) {
				throw new GameException($this->call('Fuga:Public:Account:login'));
			}

			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			if (!$gamerdoc || 
				(!$this->get('security')->isGroup('admin') && !$this->get('security')->isGroup('gamer'))) {
				throw new GameException('Вы не являетесь игроком. Для участия в игре войдите на сайт с логином и паролем игрока<br>'.$this->call('Fuga:Public:Account:login'));
			}

			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			
			if (!$gamedoc) {
				throw new GameException('Вам не назначен зал для игры. Обратитесь к администратору');
			}
			
			if (time() - $gamedoc->getFromtime() < -1800) {
				throw new GameException('Игра еще не началась');
			}
			
			$game = new Game($gamedoc, $this->get('container'));
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			
			if ($game->isState(Game::STATE_BEGIN)) {
				$game->start($gamer);
			}
			
			$rivalsdoc = $this->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->field('board')->equals($game->getId())
					->field('user')->notEqual($gamer->getId())
					->getQuery()->execute();
			$rivals = array();
			$numOfGamers = count($rivalsdoc) + 1;
			
			foreach ($rivalsdoc as $rivaldoc) {
				$rivals[] = new Rival($rivaldoc, $gamer->getRivalPosition($rivaldoc->getSeat(), $numOfGamers));
			}

			if ($game->isMover($gamer->getSeat()) || in_array($game->getStateNo(), array(4, 41, 5, 7))) {
				$game->startTimer();
			}
			if ($game->isState(1)) {
				$gamer->startTimer();
			}
			
		} catch (GameException $e) {
			$error = $e->getMessage();
			return $this->render('game/error.tpl', compact('error'));
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
		
		$this->get('container')->setVar('javascript', 'game');
		
		return $this->render('game/index.tpl', array(
			'game'   => $game,
			'gamer'  => $gamer,
			'table'  => $this->render('game/table.tpl', compact('game', 'gamer')),
			'rivals' => $this->render('game/rivals.tpl', compact('rivals', 'game')),
			'gamerData'  => $this->render('game/gamer.tpl',  compact('gamer', 'game')),
			'minbet' => $this->render('game/minbet.tpl', compact('game')),
			'bank'   => $this->render('game/bank.tpl',   compact('game')),
			'winner' => $this->render('game/winner.tpl', compact('game')),
			'hint'   => $this->render('game/hint.tpl',   compact('gamer', 'game')),
			'deck'   => new Deck(),
		));
	}
	
	public function answerAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			
			$answerNo = $this->get('util')->post('answer', true, 0);
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			$game = new Game($gamedoc, $this->get('container'));
			$gamer->answerQuestion($answerNo, $game);
			$game->change($gamer);
		} catch (GameException $e) {
			$this->get('log')->addError($e->getMessage());
		}
		
		return json_encode(array(
			'ok' => true,
			'table' => $this->render('game/table.tpl', compact('game', 'gamer')),
			'chips' => $gamer->getChips(),
			'cards' => $this->render('game/cards.tpl', compact('game', 'gamer')),
			'hint'  => $this->render('game/hint.tpl', compact('game', 'gamer')),
			'change_times' => $gamer->getTimes(),
		));
	}
	
	public function changeAction(){
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			$card = $this->get('util')->post('card_no', true, 0);
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			$game = new Game($gamedoc, $this->get('container'));
			$gamer->changeCard($card);
		} catch (GameException $e) {
			$this->get('log')->addError($e->getMessage());
		}
		
		return json_encode(array(
			'ok' => true,
			'table' => $this->render('game/table.tpl', compact('game', 'gamer')),
		));
	}
	
	public function nochangeAction(){
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			$game = new Game($gamedoc, $this->get('container'));
			
			$gamer->nochangeCard();
			$game->nochange($gamer);
		} catch (GameException $e) {
			$this->get('log')->addError($e->getMessage());
		}
		
		return json_encode(array(
			'ok' => true,
			'table' => $this->render('game/table.tpl', compact('game', 'gamer')),
			'hint' => $this->render('game/hint.tpl', compact('game', 'gamer')),
		));
	}
	
	public function foldAction(){
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			$game = new Game($gamedoc, $this->get('container'));
			$game->fold($gamer);
		} catch (GameException $e) {
			$this->get('log')->addError($e->getMessage());
			return json_encode(array('ok' => false));
		}
		
		return json_encode(array(
			'ok' => true,
			'table' => $this->render('game/table.tpl', compact('game', 'gamer')),
			'cards' => $this->render('game/cards.tpl', compact('game', 'gamer')),
			'winner'=> $this->render('game/winner.tpl', compact('game', 'gamer')),
		));
	}
	
	public function betAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			$chips = $this->get('util')->post('chips', true, 0);
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			$game = new Game($gamedoc, $this->get('container'));
			$game->bet($gamer, $chips);
		} catch (GameException $e) {
			$this->get('log')->addError($e->getMessage());
			return json_encode(array('ok' => false));
		}
		
		return json_encode(array(
			'ok' => true,
			'table' => $this->render('game/table.tpl', compact('game', 'gamer')),
			'cards' => $this->render('game/cards.tpl', compact('game', 'gamer')),
			'bank'  => $game->getBank(),
			'bets'  => $game->getBets(),
			'chips' => $gamer->getChips(),
			'bet'   => $gamer->getBet(),
			'state' => $game->getStateNo(),
			'hint'  => $this->render('game/hint.tpl', compact('game', 'gamer')),
			'winner'=> $this->render('game/winner.tpl', compact('game', 'gamer')),
		));
	}
	
	public function checkAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			$game = new Game($gamedoc, $this->get('container'));
			$game->check($gamer);
		} catch (GameException $e) {
			$this->get('log')->addError($e->getMessage());
			return json_encode(array('ok' => false));
		}
		
		return json_encode(array(
			'ok' => true,
			'table' => $this->render('game/table.tpl', compact('game', 'gamer')),
			'cards' => $this->render('game/cards.tpl', compact('game', 'gamer')),
			'bank'  => $game->getBank(),
			'bets'  => $game->getBets(),
			'chips' => $gamer->getChips(),
			'bet'   => $gamer->getBet(),
			'state' => $game->getStateNo(),
			'hint'  => $this->render('game/hint.tpl', compact('game', 'gamer')),
			'winner'=> $this->render('game/winner.tpl', compact('game', 'gamer')),
		));
	}
	
	public function distributeAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			$game = new Game($gamedoc, $this->get('container'));
			$game->distribute($gamer);
			$rivalsdoc = $this->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->field('board')->equals($game->getId())
					->field('user')->notEqual($gamer->getId())
					->getQuery()->execute();
			$rivals = array();
			$numOfGamers = count($rivalsdoc) + 1;
			foreach ($rivalsdoc as $rivaldoc) {
				$rivals[] = new Rival($rivaldoc, $gamer->getRivalPosition($rivaldoc->getSeat(), $numOfGamers));
			}
		} catch (GameException $e) {
			$this->get('log')->addError($e->getMessage());
			return json_encode(array('ok' => false));
		}
		
		$rivalsData = array();
		if (isset($rivals)) {
			foreach ($rivals as $rival) {
				$rivalsData[$rival->id] = array(
					'chips' => $rival->chips,
					'cards' => $this->render('game/rivalcards.tpl', compact('rival', 'game')),
					'bet'   => $rival->bet,
					'active' => $rival->isHere(),
				);
			}
		}
		
		return json_encode(array(
			'ok' => true,
			'table' => $gamer->question ? null : $this->render('game/table.tpl', compact('game', 'gamer')),
			'cards' => $game->isState(1)? null : $this->render('game/cards.tpl', compact('game', 'gamer')),
			'hint' => $this->render('game/hint.tpl', compact('game', 'gamer')),
			'winner' => $this->render('game/winner.tpl', compact('game', 'gamer')),
			'state' => $game->getStateNo(),
			'maxbet'=> $game->getMaxbet(),
			'gamerstate' => $gamer->getState(),
			'mover' => $game->isMover($gamer->getSeat()) ? 1 : 0,
			'rivals' => $rivalsData,
		));
	}
	
	public function endroundAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			$game = new Game($gamedoc, $this->get('container'));
			$game->endround($gamer);
		} catch (GameException $e) {
			$this->get('log')->addError($e->getMessage());
			return json_encode(array('ok' => false));
		}
		
		return json_encode(array(
			'ok' => true,
			'table' => $this->render('game/table.tpl', compact('game', 'gamer')),
			'state' => $game->getStateNo(),
		));
	}
	
	public function nextAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			$game = new Game($gamedoc, $this->get('container'));
			$game->next($gamer);
			$rivalsdoc = $this->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->field('board')->equals($game->getId())
					->field('user')->notEqual($gamer->getId())
					->getQuery()->execute();
			$rivals = array();
			$numOfGamers = count($rivalsdoc) + 1;
			foreach ($rivalsdoc as $rivaldoc) {
				$rivals[] = new Rival($rivaldoc, $gamer->getRivalPosition($rivaldoc->getSeat(), $numOfGamers));
			}
			if ($game->isMover($gamer->getSeat()) || in_array($game->getStateNo(), array(4, 41, 5, 7))) {
				$game->startTimer();
			}
			$gamer->startTimer();
		} catch (GameException $e) {
			$this->get('log')->addError($e->getMessage());
			return json_encode(array('ok' => false));
		}
		
		$rivalsData = array();
		foreach ($rivals as $rival) {
			$rivalsData[$rival->id] = array(
				'chips' => $rival->chips,
				'cards' => $this->render('game/rivalcards.tpl', compact('rival', 'game')),
				'bet'   => $rival->bet,
				'active' => $rival->isHere(),
			);
		}
		
		return json_encode(array(
			'ok' => true,
			'table' => $this->render('game/table.tpl', compact('game', 'gamer')),
			'cards' => $this->render('game/cards.tpl', compact('game', 'gamer')),
			'hint' => $this->render('game/hint.tpl', compact('game', 'gamer')),
			'winner' => $this->render('game/winner.tpl', compact('game', 'gamer')),
			'state' => $game->getStateNo(),
			'maxbet'=> $game->getMaxbet(),
			'gamerstate' => $gamer->getState(),
			'mover' => $game->isMover($gamer->getSeat()) ? 1 : 0,
			'rivals' => $rivalsData,
		));
	}
	
	public function prebuyAction(){
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			$game = new Game($gamedoc, $this->get('container'));
			$game->prebuy($gamer);
		} catch (GameException $e) {
			$this->get('log')->addError($e->getMessage());
			return json_encode(array('ok' => false));
		}
		
		return json_encode(array(
			'ok' => true,
			'table' => $this->render('game/table.tpl', compact('game', 'gamer')),
		));
	}
	
	
	public function buyAction(){
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			$game = new Game($gamedoc, $this->get('container'));
			$gamer->buyChips();
		} catch (GameException $e) {
			$this->get('log')->addError($e->getMessage());
			return json_encode(array('ok' => false));
		}
		
		return json_encode(array(
			'ok' => true,
			'table' => $this->render('game/table.tpl', compact('game', 'gamer')),
		));
	}
	
	public function buyanswerAction(){
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			$answer = $this->get('util')->post('answer', true, 0);
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			$game = new Game($gamedoc, $this->get('container'));
			$gamer->answerBuyQuestion($answer, $game);
		} catch (GameException $e) {
			$this->get('log')->addError($e->getMessage());
			return json_encode(array('ok' => false));
		}
		
		return json_encode(array(
			'ok' => true,
			'table' => $this->render('game/table.tpl', compact('game', 'gamer')),
			'chips' => $gamer->getChips(),
		));
	}
	
	public function updateAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			$gamer = new RealGamer($gamerdoc, $this->get('container'));
			$game = new Game($gamedoc, $this->get('container'));
			$rivalsdoc = $this->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->field('board')->equals($game->getId())
					->field('user')->notEqual($gamer->getId())
					->getQuery()->execute();
			$rivals = array();
			$numOfGamers = count($rivalsdoc) + 1;
			foreach ($rivalsdoc as $rivaldoc) {
				$rivals[] = new Rival($rivaldoc, $gamer->getRivalPosition($rivaldoc->getSeat(), $numOfGamers));
			}
			if ($game->isMover($gamer->getSeat()) || in_array($game->getStateNo(), array(4, 41, 5, 7))) {
				$game->startTimer();
			}
			$gamer->startTimer();
		} catch (Exception\GameException $e) {
			$this->get('log')->addError('UPDATE:'.$e->getMessage());
			return json_encode(array('ok' => false));
		}
		
		$rivalsData = array();
		foreach ($rivals as $rival) {
			$rivalsData[$rival->id] = array(
				'chips' => $rival->chips,
				'cards' => $this->render('game/rivalcards.tpl', compact('rival', 'game')),
				'bet'   => $rival->bet,
				'state'   => $rival->state,
				'active' => $rival->active ? 1 : 0,
				'allin' => $rival->allin ? 1 : 0,
			);
		}
		
		return json_encode(array(
			'ok' => true,
			'table' => $gamer->question ? null : $this->render('game/table.tpl', compact('game', 'gamer')),
			'cards' => $this->render('game/cards.tpl', compact('game', 'gamer')),
			'bank'  => $game->getBank(),
			'bets'  => $game->getBets(),
			'chips' => $gamer->getChips(),
			'bet'   => $gamer->getBet(),
			'hint'  => $this->render('game/hint.tpl', compact('game', 'gamer')),
			'winner'=> $this->render('game/winner.tpl', compact('game', 'gamer')),
			'state' => $game->getStateNo(),
			'maxbet'=> $game->getMaxbet(),
			'gamerstate' => $gamer->getState(),
			'mover' => $game->isMover($gamer->getSeat()) ? 1 : 0,
			'rivals'=> $rivalsData,
			'minbet' => $game->minbet,
		));
	}
	
	public function startAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		try {
			$gamerdoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Gamer')
					->findOneByUser(intval($user['id']));
			
			$gamedoc = $this->get('odm')
					->getRepository('\Fuga\GameBundle\Document\Board')
					->findOneByBoard($gamerdoc->getBoard());
			$game = new Game($gamedoc, $this->get('container'));
			$game->removeTimer();
		} catch (GameException $e) {
			$this->get('log')->addError($e->getMessage());
		}
		
		return json_encode(array(
			'ok' => true,
			'state' => 1,
		));
	}
	
	public function outAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/game/game');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		$state = $this->get('util')->post('state', true, 0);
		
		try {
			$this->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->findAndUpdate()
				->field('user')->equals(intval($user['id']))
				->field('state')->set($state)
				->getQuery()->execute();
		} catch (Exception\GameException $e) {
			return json_encode(array(
				'ok' => false,
				'error' => $e->getMessage(),
			));
		}
		
		return json_encode(array(
			'ok' => true,
			'state' => $state,
		));
	}
	
	public function clearAction($params) {
		$user = $this->get('security')->getCurrentUser();
		if (!$user || !$this->get('security')->isGroup('admin') ) {
			$this->get('router')->redirect('/game');
		}
		
		$gameId = intval(array_shift($params));
		$time   = array_shift($params) ?: null;
		
		if (!$gameId) {
			$this->get('router')->redirect('/game');
		}
		
		if ($time && strlen($time) == 4) {
			$hour = substr($time, 0, 2);
			$minute = substr($time, 2, 2);
			$now = new \DateTime(date('Y-m-d').' '.$hour.':'.$minute.':00');
		} else {
			$now = new \DateTime();
			$now->add(new \DateInterval('PT10S'));
		}
		$this->get('container')->updateItem('game_board', 
				array('fromtime' => $now->format('Y-m-d H:i:s')),
				array('id' => $gameId)
		);
		
		try {
			$this->get('odm')->createQueryBuilder('\Fuga\GameBundle\Document\Board')
					->remove()
					->field('board')->equals($gameId)
					->getQuery()->execute();
			
			$this->get('odm')->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->remove()
					->field('board')->equals($gameId)
					->getQuery()->execute();
			
		} catch (GameException $e) { }
		
		$this->createAction(array($gameId));
		
		$this->get('router')->redirect('/game/game');
	}
	
	public function createAction($params) {
		$user = $this->get('security')->getCurrentUser();
		if (!$user || !$this->get('security')->isGroup('admin') ) {
			$this->get('router')->redirect('/game');
		}
		
		$boardId = array_shift($params);
		if (!$boardId) {
			throw $this->createNotFoundException('');
		}
		
		$game = $this->get('odm')
				->getRepository('\Fuga\GameBundle\Document\Board')
				->findOneByBoard(intval($boardId));
		if (!$game) {
			$board = $this->get('container')->getItem('game_board', $boardId);
			$game = new Board();
			$game->setBoard($board['id']);
			$game->setName($board['name']);
			$game->setUpdated(time());
			$game->setTimer(array( array(
				'handler' => 'onStart', 
				'holder' => 'begin-timer', 
				'time' => strtotime($board['fromtime'])
			)));
			$game->setFromtime(strtotime($board['fromtime']));
			$this->get('odm')->persist($game);
		}
		
		$gamers = $this->get('container')->getItems('account_member', 'board_id='.$boardId);
		foreach ($gamers as $gamerData) {
			$gamer = $this->get('odm')
				->getRepository('\Fuga\GameBundle\Document\Gamer')
				->findOneByUser(intval($gamerData['user_id']));
			if ($gamer) {
				continue;
			}
			$gamer = new Gamer();
			$gamer->setMember($gamerData['id']);
			$gamer->setUser($gamerData['user_id']);
			$gamer->setBoard($gamerData['board_id']);
			$gamer->setName($gamerData['name']);
			$gamer->setLastname($gamerData['lastname']);
			$gamer->setSeat($gamerData['seat']);
			$gamer->setChips($gamerData['chips']);
			$gamer->setAvatar(isset($gamerData['avatar_value']['extra']) 
				? $gamerData['avatar_value']['extra']['main']['path'] 
				: '/bundles/public/img/avatar_empty.png');
			$this->get('odm')->persist($gamer);
		}
		
		$this->get('odm')->flush();
		$error = 'Игра и игроки для зала №'.$boardId.' созданы.';
			
		return $this->render('game/error.tpl', compact('error'));
	}
	
	public function syncAction() {
		if (isset($_SERVER['REQUEST_URI'])) {
			$this->get('router')->redirect('/');
		}
		
		$boards = $this->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Board')
				->field('state')->gt(0)
				->field('state')->notEqual(6)
				->getQuery()->execute();
		foreach ($boards as $gamedoc) {
			try {
				$game = new Game($gamedoc, $this->get('container'));
				$game->sync();
			} catch (\Exception $e) {
				$this->get('log')->addError('cron.ERROR:'.$e->getMessage());
			}	
		}
		
	}

}