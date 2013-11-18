<?php

namespace Fuga\CommonBundle\Model;

class User {
	
	public $tables;

	public function __construct() {

		$this->tables = array();

		$this->tables[] = array(
		'name' => 'user',
		'module' => 'user',
		'title' => 'Список пользователей',
		'order_by' => 'login',
		'fieldset' => array (
			'login' => array (
				'name' => 'login',
				'title' => 'Логин',
				'type' => 'string',
				'width' => '25%',
			),
			'password' => array (
				'name' => 'password',
				'title' => 'Пароль',
				'type' => 'password',
			),
			'hashkey' => array (
				'name' => 'hashkey',
				'title' => 'Ключ',
				'type' => 'string',
			),
			'name' => array (
				'name' => 'name',
				'title' => 'ФИО',
				'type' => 'string',
				'width' => '25%'
			),
			'email' => array (
				'name' => 'email',
				'title' => 'Эл. почта',
				'type' => 'string',
				'width' => '25%'
			),
			'group_id' => array (
				'name' => 'group_id',
				'title' => 'Группа',
				'type' => 'select',
				'l_table' => 'user_group',
				'l_field' => 'name',
				'width' => '25%'

			),
			'is_admin' => array (
				'name' => 'is_admin',
				'title' => 'Админ',
				'type' => 'checkbox',
				'width' => '1%',
				'group_update' => true
			),
			'is_active' => array (
				'name' => 'is_active',
				'title' => 'Активен',
				'type' => 'checkbox',
				'width' => '1%',
				'group_update' => true
			)	
		));

		$this->tables[] = array(
		'name' => 'group',
		'module' => 'user',
		'title' => 'Группы пользователей',
		'order_by' => 'name',
		'fieldset' => array (
			'name' => array (
				'name' => 'name',
				'title' => 'Название',
				'type' => 'string',
				'width' => '25%',
			),
			'rules' => array (
				'name' => 'rules',
				'title' => 'Права',
				'type' => 'select_list',
				'l_table' => 'config_module',
				'l_field' => 'title',
				'search' => true,
				'width' => '70%'
			)
		));
		
	}
}