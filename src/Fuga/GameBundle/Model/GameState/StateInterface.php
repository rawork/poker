<?php

namespace Fuga\GameBundle\Model\GameState;

interface StateInterface {
	
	public function startGame();
	public function changeCards($cardNo, $question);
	public function noChangeCards();
	public function answerQuestion($answerNo, $question);
	public function makeBet($chips);
	public function checkBet();
	public function allinBet();
	public function foldCards();
	public function distributeWin($questions);
	public function buyChips();
	public function answerBuyQuestion($answerNo);
	public function nextGame();
	public function endRound();
	public function endGame();
	public function stopGame();
	
}