<?php

namespace Fuga\GameBundle\Model\GameState;

interface StateInterface {
	
	public function startGame($gamer);
	public function changeCards($gamer);
	public function makeMove($gamer);
	public function distributeWin($gamer);
	public function buyChips($gamer);
	public function answerBuyQuestion($gamer);
	public function nextGame($gamer);
	public function endRound($gamer);
	public function endGame($gamer);
	public function sync($gamer);
	public function wait();
	
}