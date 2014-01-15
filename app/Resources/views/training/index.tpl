<div class="row-fluid">
	<div class="span2"><a href="/"><img src="{$theme_ref}/public/img/logo.png"></a></div>
	<div class="span8 game-time" id="game-time"></div>
	<div class="span2 game-exit"><a href="/account/logout">выйти из игры</a></div>
</div>
<div class="game-board-container">	
	<div class="game-board">
		<div class="row-fluid">
			<div class="span4 game-min-bet">{if $training->board->state > 0}Минимальная ставка: <div id="min_bet">{$training->board->minbet}</div>{/if}</div>
			<div class="span4 game-table" id="table">
				{if $training->board->state == 1}
				<div class="game-change">
					<div class="joker-message">Вы можете поменять до 2-х карт, ответив на вопрос. Выберите карты, щелкнув на них мышью. В случае неправильного ответа вы теряете фишки</div>
					<input class="btn btn-primary btn-xs" type="button" value="Готов менять">
					<input class="btn btn-danger btn-xs" type="button" value="Не меняю">
				</div>
				{else}
				<div class="game-flop">	
					{foreach from=$training->board->flop item=card}
					<div class="card" data-card-name="{$card.name}">
						{if $training->board->state > 2}<img src="/bundles/public/img/cards/{$card.name}.png" />
						{else}
						<img src="/bundles/public/img/shirt.png" />
						{/if}
					</div>
					{/foreach}
				</div>
				{/if}	
				<div class="game-timer" id="game-timer"></div>
			</div>
			<div class="span4 game-main-bank">
				{if $training->board->state > 0}<div>Банк игры: <div id="bank">{$training->board->bank}</div></div>{/if}
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
</div>		
<div class="gamer-container">
	{foreach from=$training->bots item=gamer}
	<div class="gamer{$gamer->position}">
		{if $gamer->position == 1 || $gamer->position == 5}
		<div class="gamer-cards">
			{foreach from=$gamer->cards item=card}
			<div class="card" data-card-name="{$card.name}">
				{if $training->board->state == 4}<img src="/bundles/public/img/cards/{$card.name}.png" />
				{else}
				<img src="/bundles/public/img/shirt.png" />
				{/if}
			</div> 
			{/foreach}
		</div>
		{/if}
		<div class="pull-left gamer-avatar"><img src="{$gamer->avatar}" /></div>
		<div class="gamer-status gamer-status-ready">Активен</div>
		<div class="gamer-name">{$gamer->name}<br>{$gamer->lastname}</div>
		<div class="clearfix"></div>
		<div class="gamer-info"><img src="/bundles/public/img/chips.png"> Фишки: <span class="gamer-chips" id="chips{$gamer->position}">{$gamer->chips}</span> <img src="/bundles/public/img/bet.png"> Ставка: <span class="gamer-bet" id="bet{$gamer->position}">{$gamer->bet}</span> </div>
		{if $gamer->position > 1 && $gamer->position < 5}
		<div class="gamer-cards">
			{foreach from=$gamer->cards item=card}
			<div class="card" data-card-name="{$card.name}">
				{if $training->board->state == 4}<img src="/bundles/public/img/cards/{$card.name}.png" />
				{else}
				<img src="/bundles/public/img/shirt.png" />
				{/if}
			</div> 
			{/foreach}
		</div>
		{/if}
	</div>
	{/foreach}
	<div class="gamer">
		<div class="gamer-cards" id="gamer-cards">
			{foreach from=$training->gamer->cards key=k item=card}
			<div class="card" data-card-name="{$card.name}" data-card-id="{$k}"><img src="/bundles/public/img/cards/{$card.name}.png" /></div> 
			{/foreach}
			<div class="clearfix"></div>
		</div>
		<div class="pull-left gamer-avatar"><img src="{$training->gamer->avatar}" /></div>
		<div class="gamer-status gamer-status-ready">Активен</div>
		<div class="gamer-name">{$training->gamer->name}<br>{$training->gamer->lastname}</div>
		<div class="clearfix"></div>
		<div class="gamer-info"><img src="/bundles/public/img/chips.png"> Фишки: <span class="gamer-chips" id="chips">{$training->gamer->chips}</span> <img src="/bundles/public/img/bet.png"> Ставка: <span class="gamer-bet" id="bet">{$training->gamer->bet}</span> </div>
	</div>
	<div class="game-buttons-container">
		<div class="game-arrow"><img src="/bundles/public/img/arrow.png" /></div>
		<div class="game-buttons">
			Введите сумму:
			<input type="text" id="input_bet" value="{$training->board->minbet}">
			<input class="btn btn-warning" data-move="vabank" type="button" value="Ва-банк">
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input class="btn btn-success" data-move="bet" type="button" value="Ставка">
			<input class="btn btn-primary" data-move="check" type="button" value="Чек">
			<input class="btn btn-danger" data-move="fold" type="button" value="Пас">
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input class="btn btn-warning" data-move="new" type="button" value="Заново">
		</div>
	</div>
	{foreach from=$training->board->winner item=combination}
	<div class="game-winner winner{$combination.position}">Победитель - {$combination.name}</div>
	{/foreach}
</div>
<div class="game-combinations"><img src="{$theme_ref}/public/img/combinations2.jpg"></div>
<div {if $training->board->state != 11}class="closed"{/if} id="game-question">{$question}</div>
<div {if $training->board->state != 0}class="closed"{/if} id="game-start">{$start}</div>
<script type="text/javascript">
	// common game parameters
	var gamestate = {$training->board->state};
	var gamemaxbet = {$training->board->maxbet};
	var gameallin = {$training->board->allin};
	var gamerbet = {$training->gamer->bet};

	// training start
	var gametraining = true;
</script>

	
