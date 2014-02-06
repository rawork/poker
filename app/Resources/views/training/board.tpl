{if $training->isState(0)}
<div class="game-message-container">
	<div class="game-message game-start">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">Приветствуем Вас<br>в тренировочном зале!</div>
				<button class="btn btn-success btn-large" data-action="start">Раздать<small>карты</small></button>
			</div>
		</div>
	</div>
</div>
{elseif $training->isState(1)}
<div class="game-message-container">
	<div class="game-message">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">
{if $training->changes == 2}Вы можете поменять одну  карту, ответив на вопрос в течение
13 секунд. Кликните карту, которую хотите заменить, и нажмите "меняю". В 
случае неправильного ответа вы теряете одну фишку.
{else}Вы можете поменять еще одну  карту, ответив на вопрос в течение 13 
секунд. Кликните карту, которую хотите заменить, и нажмите "меняю". В 
случае неправильного ответа вы теряете одну фишку.{/if}</div>
				<button class="btn btn-primary" data-action="change">Меняю</button>
				<button class="btn btn-danger" data-action="nochange">Не меняю</button>
				<div class="timer" id="change-timer"></div>
			</div>
		</div>
	</div>
</div>
{elseif $training->isState(11)}
{if $training->gamer->question}
<div class="game-question-container">
	<div class="game-question">
		<div class="question-body">
			<div class="title">{$training->gamer->question.name}:</div>
			<ul class="question-answer">
				<li><i data-answer-id="1"></i> {$training->gamer->question.answer1}</li>
				<li><i data-answer-id="2"></i> {$training->gamer->question.answer2}</li>
				<li><i data-answer-id="3"></i> {$training->gamer->question.answer3}</li>
				<li><i data-answer-id="4"></i> {$training->gamer->question.answer4}</li>
			</ul>	
		</div>
		<div class="question-footer">
			<button class="btn btn-warning" data-action="answer">ОТВЕТИТЬ</button> 
			<div class="question-timer">Время на ответ: <span id="answer-timer"></span></div>
		</div>
	</div>
</div>
{else}
Ошибка! Вопрос не выбран.
{/if}
{elseif $training->isState(2) || $training->isState(3) || $training->isState(4)}
<div class="game-flop">	
	{foreach from=$training->flop item=card}
	<div class="card{if $training->isState(4) && $training->combination[$card.name]} active{/if}{if ($training->isState(2) || $training->isState(3)) && $training->gamer->combination[$card.name]} hint{/if}" data-card-name="{$card.name}">
		{if $training->isState(3) || $training->isState(4)}<img src="/bundles/public/img/cards/{$card.name}.png" />
		{else}
		<img src="/bundles/public/img/shirt.png" />
		{/if}
	</div>
	{/foreach}
</div>
<div class="game-timer" id="game-timer"></div>
{elseif $training->isState(41)}
<div class="game-message-container">
	<div class="game-message">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">Поскольку я выпал игроку за этим столом, все получают приз! Поздравляю!</div>
				<div class="timer" id="joker-timer"></div>
			</div>
		</div>
	</div>
</div>
{elseif $training->isState(42)}
<div class="game-message-container">
	<div class="game-message">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">Если у вас закончились фишки, но остался азарт, вы можете купить фишки 
в казино, отвечая на вопросы. Касса работает в течение 2 минут. За 
каждый правильный ответ вы получаете количество фишек, равное 
минимальной ставке на момент игры.</div>
				<div class="timer" id="prebuy-timer"></div>
			</div>
		</div>
	</div>
</div>
{elseif $training->isState(5)}
{if $training->gamer->question}
<div class="game-question-container">
	<div class="game-question">
		<div class="question-body">
			<div class="title">{$training->gamer->question.name}:</div>
			<ul class="question-answer">
				<li><i data-answer-id="1"></i> {$training->gamer->question.answer1}</li>
				<li><i data-answer-id="2"></i> {$training->gamer->question.answer2}</li>
				<li><i data-answer-id="3"></i> {$training->gamer->question.answer3}</li>
				<li><i data-answer-id="4"></i> {$training->gamer->question.answer4}</li>
			</ul>	
		</div>
		<div class="question-footer">
			<button class="btn btn-warning" data-action="buyanswer">ОТВЕТИТЬ</button> <a data-action="nobuyanswer">без ответа</a> 
			<div class="row-fluid">
				<div class="span6 question-timer">Время на ответ: <span id="buy-timer"></span></div>
				<div class="span6 question-times">Попытка <span id="question-times">{$training->gamer->question.number}</span> из <span id="question-max-times">3</span></div>
			</div>
		</div>
	</div>
</div>
{/if}
{elseif $training->isState(6)}
<div class="game-message-container">
	<div class="game-message game-end">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">{if $training->gamer->chips > 0}Поздравляем, Вы выиграли все фишки :){else}Вы проиграли все фишки и выбываете из игры. 
В реальной игре вам нужно 
было заранее докупить фишки. Здесь вы можете потренироваться, начав 
заново, в том числе испробовать покупку фишек. {/if}</div>
				<button class="btn btn-primary" data-action="start">Начать заново</button>
				<button class="btn btn-danger" data-action="stop">Выйти</button>
				<div class="timer" id="change-timer"></div>
			</div>
		</div>
	</div>
</div>
{elseif $training->isState(7)}
<div class="game-message-container">
	<div class="game-message game-end">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">Раунд завершен. Можно продолжить тренироваться или выйти из игры</div>
				<button class="btn btn-primary" data-action="next">Продолжить</button>
				<button class="btn btn-danger" data-action="stop">Выйти</button>
				<div class="timer" id="round-end-timer"></div>
			</div>
		</div>
	</div>
</div>
{/if}