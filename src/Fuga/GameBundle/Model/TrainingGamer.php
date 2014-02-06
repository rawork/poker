<?php

namespace Fuga\GameBundle\Model;

class TrainingGamer extends AbstractGamer {
	
	public $question;
	public $buying;
	public $change;
	public $rank;
	public $combination;
	
	public function __construct($gamer, array $options = array()) {
		$this->data = array(
			'id'      => $gamer['user_id'],
			'avatar'  => isset($gamer['avatar_value']['extra']) ? $gamer['avatar_value']['extra']['main']['path'] : '/bundles/public/img/avatar_empty.png',
			'name'    => $gamer['name'],
			'lastname'=> $gamer['lastname'],
			'bet'     => 0,
			'seat'    => 6,
			'position'=> 0,
		);
		$this->chips = isset($options['chips']) ? intval($options['chips']) : 10;
	}
	
}
