{if !$training->isState(0)}<div class="gamer" id="gamer">
	<div class="gamer-cards" id="gamer-cards">
		{foreach from=$training->gamer->cards key=k item=card}
		<div class="card{if $training->isState(1)} choose{/if}{if $training->isState(4) && $training->board->combination[$card.name]} active{/if}" data-card-name="{$card.name}" data-card-id="{$k}"><img src="/bundles/public/img/cards/{$card.name}.png" /></div> 
		{/foreach}
	</div>
	<div class="gamer-title-container">
		<div class="gamer-title">
			<div class="gamer-avatar pull-left"><img src="{$training->gamer->avatar}" /></div>
			<div class="gamer-status gamer-status-ready">Активен</div>
			<div class="gamer-name">{$training->gamer->name}<br>{$training->gamer->lastname}</div>
		</div>
		<div class="gamer-info">
			<div><img src="/bundles/public/img/chips.png"> Фишки: <span class="gamer-chips" id="chips">{$training->gamer->chips}</span></div>
			<div><img src="/bundles/public/img/bet.png"> Ставка: <span class="gamer-bet" id="bet">{$training->gamer->bet}</span> </div>
		</div>
		<div class="clearfix"></div>
	</div>	
</div>
<div class="game-buttons-container">
	<div class="game-arrow"><img src="/bundles/public/img/arrow.png" /></div>
	<div class="game-buttons">
		Введите сумму:
		<input type="text" id="input_bet" value="{$training->board->minbet}">
		<button class="btn btn-warning" data-move="vabank">Ва-банк</button>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<button class="btn btn-success" data-move="bet">Ставка</button>
		<button class="btn btn-primary" data-move="check">Чек</button>
		<button class="btn btn-danger" data-move="fold">Пас</button>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<button class="btn btn-primary btn-buy" data-move="buy">Покупка фишек</button>
		<button class="btn btn-warning" data-move="new">Заново</button>
	</div>
</div>{/if}