<?php

namespace Fuga\GameBundle\Model;

class TrainingGamer extends AbstractGamer {
	
	public $question;
	public $buying;
	public $change;
	
	public function __construct($gamer, array $options = array()) {
		$this->data = array(
			'id'      => $gamer['user_id'],
			'avatar'  => isset($gamer['avatar_value']['extra']) ? $gamer['avatar_value']['extra']['main']['path'] : '/bundles/public/img/avatar_empty.png',
			'name'    => $gamer['name'],
			'lastname'=> $gamer['lastname'],
			'chips'   => isset($options['chips']) ? $options['chips'] : 10,
			'bet'     => 0,
			'seat'    => 6,
			'position'=> 0,
		);
	}
	
	public function emptyBet() {
		$this->bet = 0;
	}
	
}
