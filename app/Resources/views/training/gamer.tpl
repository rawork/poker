{if $training->board->state != 0}<div class="gamer">
	<div class="gamer-cards" id="gamer-cards">
		{foreach from=$training->gamer->cards key=k item=card}
		<div class="card{if $training->board->state == 4 && $training->board->combination[$card.name]} active{/if}" data-card-name="{$card.name}" data-card-id="{$k}"><img src="/bundles/public/img/cards/{$card.name}.png" /></div> 
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
</div>{/if}