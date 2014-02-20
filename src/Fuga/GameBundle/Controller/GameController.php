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

			$gamer0 = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
			if (!$gamer0 || 
				(!$this->get('security')->isGroup('admin') && !$this->get('security')->isGroup('gamer'))) {
				throw new GameException('Вы не являетесь игроком. Для участия в игре войдите на сайт с логином и паролем игрока<br>'.$this->call('Fuga:Public:Account:login'));
			}

			$board = $this->get('container')->getItem('game_board', $gamer0['board_id']);
			if (!$board) {
				throw new GameException('Вам не назначен зал для игры. Обратитесь к администратору');
			}
			
			if (time() - strtotime($board['fromtime']) < - 7200) {
				throw new GameException('Игра еще не началась');
			}
			
			$game = new Game($board['id'], $this->get('container'));
			$gamer = new RealGamer($user['id'], $this->get('container'));
			
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
			$answerNo = $this->get('util')->post('answer', true, 0);
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$game = new Game($gamer->getBoard(), $this->get('container'));
			$gamer->answerQuestion($answerNo, $game);
			$game->change($gamer);
		} catch (GameException $e) {
			$this->get('log')->write($e->getMessage());
			$this->get('log')->write($e->getTraceAsString());
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
			$card = $this->get('util')->post('card_no', true, 0);
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$game = new Game($gamer->getBoard(), $this->get('container'));
			$gamer->changeCard($card);
		} catch (GameException $e) {
			$this->get('log')->write($e->getMessage());
			$this->get('log')->write($e->getTraceAsString());
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
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$game = new Game($gamer->getBoard(), $this->get('container'));
			
			$gamer->nochangeCard();
			$game->nochange($gamer);
		} catch (GameException $e) {
			$this->get('log')->write($e->getMessage());
			$this->get('log')->write($e->getTraceAsString());
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
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$game = new Game($gamer->getBoard(), $this->get('container'));
			$game->fold($gamer);
		} catch (GameException $e) {
			$this->get('log')->write($e->getMessage());
			$this->get('log')->write($e->getTraceAsString());
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
			$chips = $this->get('util')->post('chips', true, 0);
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$game = new Game($gamer->getBoard(), $this->get('container'));
			$game->bet($gamer, $chips);
		} catch (GameException $e) {
			$this->get('log')->write($e->getMessage());
			$this->get('log')->write($e->getTraceAsString());
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
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$game = new Game($gamer->getBoard(), $this->get('container'));
			$game->check($gamer);
		} catch (GameException $e) {
			$this->get('log')->write($e->getMessage());
			$this->get('log')->write($e->getTraceAsString());
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
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$game = new Game($gamer->getBoard(), $this->get('container'));
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
			$this->get('log')->write($e->getMessage());
			$this->get('log')->write($e->getTraceAsString());
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
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$game = new Game($gamer->getBoard(), $this->get('container'));
			$game->endround($gamer);
		} catch (GameException $e) {
			$this->get('log')->write($e->getMessage());
			$this->get('log')->write($e->getTraceAsString());
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
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$game = new Game($gamer->getBoard(), $this->get('container'));
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
			$this->get('log')->write($e->getMessage());
			$this->get('log')->write($e->getTraceAsString());
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
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$game = new Game($gamer->getBoard(), $this->get('container'));
			$game->prebuy($gamer);
		} catch (GameException $e) {
			$this->get('log')->write($e->getMessage());
			$this->get('log')->write($e->getTraceAsString());
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
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$game = new Game($gamer->getBoard(), $this->get('container'));
			$gamer->buyChips();
		} catch (GameException $e) {
			$this->get('log')->write($e->getMessage());
			$this->get('log')->write($e->getTraceAsString());
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
			$answer = $this->get('util')->post('answer', true, 0);
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$game = new Game($gamer->getBoard(), $this->get('container'));
			$gamer->answerBuyQuestion($answer, $game);
		} catch (GameException $e) {
			$this->get('log')->write($e->getMessage());
			$this->get('log')->write($e->getTraceAsString());
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
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$gamer->setUpdated(time());
			$gamer->save();
			$game = new Game($gamer->getBoard(), $this->get('container'));
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
			$this->get('log')->write('UPDATE:'.$e->getMessage());
		}
		
		$rivalsData = array();
		foreach ($rivals as $rival) {
			$rivalsData[$rival->id] = array(
				'chips' => $rival->chips,
				'cards' => $this->render('game/rivalcards.tpl', compact('rival', 'game')),
				'bet'   => $rival->bet,
				'active' => $rival->isHere() ? 1 : 0,
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
			$gamer = new RealGamer($user['id'], $this->get('container'));
			$game = new Game($gamer->getBoard(), $this->get('container'));
			$game->removeTimer();
		} catch (GameException $e) {
			$this->get('log')->write($e->getMessage());
			$this->get('log')->write($e->getTraceAsString());
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
		
		$gameId = array_shift($params);
		$time   = array_shift($params) ?: '10S';
		
		if (!$gameId) {
			$this->get('router')->redirect('/game');
		}
		
		$now = new \DateTime();
		$new = $now->add(new \DateInterval('PT'.strtoupper($time)));
		
		$this->get('container')->updateItem('game_board', 
				array('fromtime' => $new->format('Y-m-d H:i:s')),
				array('id' => $gameId)
		);
		
		try {
			$game = new Game($gameId, $this->get('container'));
			$game->clear();
			$game->save();

			$gamers = $this->get('container')->getItems('account_member', 'board_id='.$gameId);
			foreach ($gamers as $gamerData) {
				$gamer = new RealGamer($gamerData['user_id'], $this->get('container'));
				$gamer->clear();
				$gamer->save();
			}
		} catch (GameException $e) {
			
		}
		
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
			$gamer->setUpdated(time());
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
		
		$this->get('log')->write('CRON_START');
		$boards = $this->get('odm')
				->getRepository('\Fuga\GameBundle\Document\Board')
				->findAll();
		foreach ($boards as $board) {
			$timer = $board->getTimer();
			if ($timer) {
				$timer = array_shift($timer);
				if (intval($timer) <= time()) {
					try {
						$game = new Game($board->getBoard(), $this->get('container'));
						$game->removeTimer();
						$game->save();
						$gamerdoc = $this->get('odm')
								->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
								->field('board')->equals($game->getId())
								->field('seat')->equals($board->getMover())
								->getQuery()->getSingleResult();
						if (!$gamerdoc) {
							throw new \Exception('no gamer to sync timer');
						}	
						$gamer = new RealGamer($gamerdoc->getUser(), $this->get('container'));
						$game->sync($gamer);
					} catch (\Exception $e) {
						$this->get('log')->write('CRON_ERROR:'.$e->getMessage());
					}	
				}
				
			}
		}
		
		$this->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->findAndUpdate()
				->field('updated')->lt(time() - 30)
				->field('timer')->set(array())
				->getQuery()->execute();
		
	}

}