<div class="row-fluid">
	<div class="span2"></div>
	<div class="span8 game-time" id="game-time"></div>
	<div class="span2 game-exit"><a href="/account/logout">выйти из игры</a></div>
</div>
<div class="game-board">
	<div class="row-fluid">
		<div class="span4 game-min-bet">Минимальная ставка: <div id="min_bet">{$board.minbet}</div></div>
		<div class="span4 game-flop" id="flop">
			{if $board.state == 1}
			<div class="gamer-change">
				<div class="joker-message">Вы можете поменять до 2 карт, ответив на вопрос. Выберите карты, щелкнув на них мышью. В случае неправильного ответа вы теряете фишки</div>
				<input class="btn btn-primary btn-xs" type="button" value="Готов менять">
				<input class="btn btn-danger btn-xs" type="button" value="Не меняю">
			</div>
			{else}
				{foreach from=$board.flop item=card}
				<div class="game-flop-card">
					{if $board.state > 2}<img src="/bundles/public/img/cards/{$card.name}.png" />{/if}
				</div>
				{/foreach}
				<div class="clearfix"></div>
			{/if}	
		</div>
		<div class="span4 game-main-bank">
			<div>Банк игры: <div id="bank">{$board.bank}</div></div>
			<div>Текущие ставки: <div id="bets">{$board.bets}</div></div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="gamer-container">
	{foreach from=$gamers item=gamer key=k}
	<div class="gamer{$k}">
		{if $k == 1 || $k == 5}
		<div class="gamer-cards">
			{foreach from=$gamer.cards item=card}
			<div class="card"></div> 
			{/foreach}
			<div class="clearfix"></div>
		</div>
		{/if}
		<div class="pull-left gamer-avatar"><img src="{$gamer.avatar}" /></div>
		<div class="gamer-status gamer-status-ready">Активен</div>
		<div class="gamer-name">{$gamer.name}<br>{$gamer.lastname}</div>
		<div class="clearfix"></div>
		<div class="gamer-info"><img src="/bundles/public/img/chips.png"> Фишки: <span class="gamer-chips" id="chips{$k+1}">{$gamer.chips}</span> <img src="/bundles/public/img/bet.png"> Ставка: <span class="gamer-bet" id="bet{$k+1}">{$gamer.bet}</span> </div>
		{if $k > 1 && $k < 5}
		<div class="gamer-cards">
			{foreach from=$gamer.cards  item=card}
			<div class="card"></div> 
			{/foreach}
			<div class="clearfix"></div>
		</div>
		{/if}
	</div>
	{/foreach}
	<div class="gamer">
		<div class="gamer-cards" id="gamer-cards">
			{foreach from=$gamer0.cards  item=card}
			<div class="card"><img src="/bundles/public/img/cards/{$card.name}.png" /></div> 
			{/foreach}
			<div class="clearfix"></div>
		</div>
		<div class="pull-left gamer-avatar"><img src="{$gamer0.avatar}" /></div>
		<div class="gamer-status gamer-status-ready">Активен</div>
		<div class="gamer-name">{$gamer0.name}<br>{$gamer0.lastname}</div>
		<div class="clearfix"></div>
		<div class="gamer-info"><img src="/bundles/public/img/chips.png"> Фишки: <span class="gamer-chips" id="chips">{$gamer0.chips}</span> <img src="/bundles/public/img/bet.png"> Ставка: <span class="gamer-bet" id="bet">{$gamer0.bet}</span> </div>
	</div>
	<div class="game-buttons-container">
		<div class="game-arrow"><img src="/bundles/public/img/arrow.png" /></div>
		<div class="game-buttons">
			Введите сумму:
			<input type="text" id="input_bet" value="1">
			<input class="btn btn-warning btn-xs" type="button" value="Ва-банк">
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input class="btn btn-success btn-xs" type="button" value="Ставка">
			<input class="btn btn-primary btn-xs" type="button" value="Чек">
			<input class="btn btn-danger btn-xs" type="button" value="Пас">
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input class="btn btn-warning btn-xs" type="button" value="Отошел">
		</div>
	</div>
</div>
<script type="text/javascript">
	var gamehour = {$board.hour};
	var gameminute = {$board.minute};
	var gamesecond = {$board.second};
	var gametraining = true;
</script>

	
