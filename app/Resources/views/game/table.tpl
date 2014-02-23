{if !$gamer->isActive()}
<div class="game-message-container">
	<div class="game-message game-end">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">У вас недостаточно средств для продолжения игры.<br> Сожалеем, вы проиграли
                    в ANCOR HOLDEM (:, желаем взять реванш на тотализаторе :)</div>
			</div>
		</div>
	</div>
</div>
{elseif $gamer->isActive() && $game->isState(6)}
<div class="game-message-container">
	<div class="game-message game-end">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">У вас больше нет соперников :).<br> Поздравляем, вы победитель и переходите
                    в следующий тур!</div>
			</div>
		</div>
	</div>
</div>	
{elseif $game->isState(0)}
<div class="game-message-container">
	<div class="game-message game-start">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">Приветствуем Вас.<br>Игра скоро начнется!</div>
				<div class="timer" id="begin-timer"></div>
			</div>
		</div>
	</div>
</div>
{elseif $game->isState(1)}
	{if $gamer->question}
	<div class="game-question-container">
		<div class="game-question">
			<div class="question-body">
				<div class="title">{$gamer->question.name}:</div>
				<ul class="question-answer">
					<li><i data-answer-id="1"></i> {$gamer->question.answer1}</li>
					<li><i data-answer-id="2"></i> {$gamer->question.answer2}</li>
					<li><i data-answer-id="3"></i> {$gamer->question.answer3}</li>
					<li><i data-answer-id="4"></i> {$gamer->question.answer4}</li>
				</ul>	
			</div>
			<div class="question-footer">
				<button class="btn btn-warning" data-action="answer">ОТВЕТИТЬ</button> 
				<div class="question-timer">Время на ответ: <span id="answer-timer"></span></div>
			</div>
		</div>
	</div>
	{elseif $gamer->getTimes() > 0}
	<div class="game-message-container">
		<div class="game-message">
			<div class="row-fluid">
				<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
				<div class="span8">
					<div class="text">
					{if $gamer->getTimes() == 2}Вы можете поменять одну  карту, ответив на вопрос в течение
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
	{else}	
	<div class="game-message-container">
		<div class="game-message">
			<div class="row-fluid">
				<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
				<div class="span8">
					<div class="text">Ожидайте, другие игроки еще не закончили менять карты!</div>
				</div>
			</div>
		</div>
	</div>	
	{/if}
{elseif $game->isState(2) || $game->isState(3) || $game->isState(4)}
<div class="game-flop">	
	{foreach from=$game->getFlop() item=card}
	<div class="card{if ($game->isState(3) || $game->isState(4)) && $gamer->isCombination($card.name)} hint{/if}{if $game->isState(4) && $game->isCombination($card.name)} active{/if}">
		{if $game->isState(3) || $game->isState(4)}<img src="/bundles/public/img/cards/{$card.name}.png" />
		{else}
		<img src="/bundles/public/img/shirt.png" />
		{/if}
	</div>
	{/foreach}
</div>
<div class="game-timer" id="game-timer"></div>
{if !$game->isMover($gamer->getSeat()) && !$game->isState(4)}<div class="game-wait">Ждите, другой игрок делает ход</div>{/if}
{elseif $game->isState(41)}
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
{elseif $game->isState(5)}
{if $gamer->question}
<div class="game-question-container">
	<div class="game-question">
		<div class="question-body">
			<div class="title">{$gamer->question.name}:</div>
			<ul class="question-answer">
				<li><i data-answer-id="1"></i> {$gamer->question.answer1}</li>
				<li><i data-answer-id="2"></i> {$gamer->question.answer2}</li>
				<li><i data-answer-id="3"></i> {$gamer->question.answer3}</li>
				<li><i data-answer-id="4"></i> {$gamer->question.answer4}</li>
			</ul>	
		</div>
		<div class="question-footer">
			<button class="btn btn-warning" data-action="buyanswer">ОТВЕТИТЬ</button> <a data-action="nobuyanswer">без ответа</a> 
			<div class="row-fluid">
				<div class="span6 question-timer">Время на ответ: <span id="buy-timer"></span></div>
				<div class="span6 question-times">Попытка <span id="question-times">{$gamer->question.number}</span> из <span id="question-max-times">3</span></div>
			</div>
		</div>
	</div>
</div>
{elseif $gamer->getBuy()}
<div class="game-message-container">
	<div class="game-message">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">Вы можете купить фишки 
в казино, отвечая на вопросы. Касса работает в течение 2 минут. За 
каждый правильный ответ вы получаете количество фишек, равное 
минимальной ставке на момент игры.</div>
				<div class="timer" id="buy-timer"></div>
			</div>
		</div>
	</div>
</div>	
{else}	
<div class="game-message-container">
	<div class="game-message">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">Ожидайте, другие игроки покупают фишки!</div>
				<div class="timer" id="buy-timer"></div>
			</div>
		</div>
	</div>
</div>	
{/if}
{elseif $game->isState(7)}
<div class="game-message-container">
	<div class="game-message game-end">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">Раунд завершен. Можете немного отдохнуть.</div>
				<div class="timer" id="round-end-timer"></div>
			</div>
		</div>
	</div>
</div>
{elseif $game->isState(8)}
<div class="game-message-container">
	<div class="game-message game-end">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">Все соперники отлучились. Ждемс.</div>
				<div class="timer" id="wait-timer"></div>
			</div>
		</div>
	</div>
</div>
{/if}