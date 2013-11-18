<?php

namespace Fuga\Component;

class Translator {
	
	private $locale;
	private $messages= array(
		'main_title' => array(
			'ru' => 'Благотворительная акция Добрый новый год с АНКОРом',
			'ua' => 'Благодійна акція Добрий новий рік з АНКОРом',
		),
		'main_button' => array(
			'ru' => 'Узнать, что нужно /<br> посоветовать лучшее',
			'ua' => 'Дізнатись, що потрібно /<br> порадити краще',
		),
		'Send' => array(
			'ru' => 'Отправить',
			'ua' => 'Відправити',
		),
		'Home' => array(
			'ru' => 'Главная',
			'ua' => 'Головна',
		),
		'Partners' => array(
			'ru' => 'Партнер',
			'ua' => 'Партнер',
		),
		'Beneficiaries' => array(
			'ru' => 'Благополучатель',
			'ua' => 'Вигодонабувач',
		),
		'The catalog of useful things' => array(
			'ru' => 'Каталог полезных вещей',
			'ua' => 'Каталог корисних речей',
		),
		'The list<br>of needed<br>goods' => array(
			'ru' => 'Список<br>необходимых<br>товаров',
			'ua' => 'Список<br>необхідних<br>товарів',
		),
		'The list of needed goods' => array(
			'ru' => 'Список необходимых товаров',
			'ua' => 'Список необхідних товарів',
		),
		'I give' => array(
			'ru' => 'Я дарю!',
			'ua' => 'Я дарую!',
		),
		'Recommend' => array(
			'ru' => 'Посоветовать',
			'ua' => 'Порадити',
		),
		'Recommendation' => array(
			'ru' => 'Совет',
			'ua' => 'Поради',
		),
		'Thanks for the gift' => array(
			'ru' => 'Спасибо за подарок',
			'ua' => 'Дякую за подарунок',
		),
		'Add useful thing' => array(
			'ru' => 'Добавить полезную вещь',
			'ua' => 'Додати корисну річ',
		),
		'Add<br>useful<br>thing' => array(
			'ru' => 'Добавить<br>полезную вещь',
			'ua' => 'Додати<br>корисну річ',
		),
		'My name is / I represent a company' => array(
			'ru' => 'Меня зовут / я представляю компанию',
			'ua' => 'Моє ім`я / я представляю компанію',
		),
		'Contact phone' => array(
			'ru' => 'Город / Контактный телефон',
			'ua' => 'Місто / Контактний телефон',
		),
		'Thing that I give the children' => array(
			'ru' => 'Вещь (вещи), которые я (моя компания) дарю детям',
			'ua' => 'Річ (речі), які я (моя компанія) дарує дітям',
		),
		'Text ads' => array(
			'ru' => 'Текст объявления (перечень и описание вещей)',	
			'ua' => 'Текст оголошення (перелік та опис речей)',
		),
		'Upload foto' => array(
			'ru' => 'Загрузить фото',	
			'ua' => 'Завантажити фото',
		),
		'Fields are required' => array(
			'ru' => 'Поля, обязательные для заполнения',	
			'ua' => 'Поля, обов`язкові для заповнення',
		),
		'Edit' => array(
			'ru' => 'Редактировать',	
			'ua' => 'Редагувати',
		),
		'Reset' => array(
			'ru' => 'Очистить форму', 
			'ua' => 'Очистити форму',
		),
		'I have a question / suggestion organizing committee shares' => array(
			'ru' => 'У меня вопрос/предложение<br>оргкомитету акции', 
			'ua' => 'Маю питання/пропозицію<br>до оргкомітету акції',
		),
		'Add comment' => array(
			'ru' => 'Комментировать',
			'ua' => 'Коментувати',
		),
		'Comment' => array(
			'ru' => 'Комментарий',
			'ua' => 'Комертар',
		),
		'Name' => array(
			'ru' => 'Имя',
			'ua' => 'Ім`я',
		),
		'Organizing committee' => array(
			'ru' => 'Оргкомитет акции',
			'ua' => 'Оргкомітет акції',
		),
		'Ancor main site' => array(
			'ru' => 'Сайт АНКОРа',
			'ua' => 'Сайт АНКОРу',
		),
		'Back to catalog beneficiaries' => array(
			'ru' => 'Вернуться<br>в каталог<br>благополучателей',
			'ua' => 'Повернутися<br>до каталогу<br>вигодонабувачів',
		),
		'More recommend' => array(
			'ru' => 'Еще советы',
			'ua' => 'больше порад',
		),
		'I want to recommend' => array(
			'ru' => 'Я хочу посоветовать',
			'ua' => 'Хочу порадити',
		),
		'Thank you for the gift of children' => array(
			'ru' => '<stong>Спасибо за подарок детям!</strong> Полезная вещь добавлена в каталог.<br> После проверки модератором информация будет опубликована на сайте.',
			'ua' => '<stong>Дякуємо за подарунок дітям!</strong> Корисну річ додано до каталогу.<br> Після перевірки модератором інформація буде опублікована на сайті.',
		),
		'The maximum amount of photos 1MB.' => array(
			'ru' => 'Максимальный объём фотографии 1 Мб',
			'ua' => 'Максимальний обсяг фотографії - 1 МБ',
		),
		'Close' => array(
			'ru' => 'Закрыть',
			'ua' => 'Закрити',
		),
		'Thank you for taking part in the action' => array(
			'ru' => '<p>Спасибо за участие в акции!</p><p>Ваш голос будет учтен<br> при проведении закупок<br>товаров для детей.</p>',
			'ua' => '<p>Дякуємо Вам за участь в акції!</p><p>Ваш голос буде зарахований<br>при проведенні закупівель<br>товарів для дітей.</p>',
		),
		'Thanks again' => array(
			'ru' => '<p>Вы уже распорядились<br>своим подарком<br>и проголосовали ранее.</p><p>Спасибо за участие в акции!</p>',
			'ua' => '<p>Ви вже розпорядилися<br>своїм подарунком<br>і проголосували раніше.</p><p>Дякуємо Вам за участь в акції!</p>',
		),
	);
			
	public function __construct($locale) {
		$this->locale = $locale;
	}
	
	public function import(array $messages) {
		foreach ($messages as $name => $value) {
			$this->$messages[$name] = array($this->locale => $value);	
		} 
	}
	
	public function t($name) {
		return isset($this->messages[$name][$this->locale]) ? $this->messages[$name][$this->locale] : $name;
	}
	
}