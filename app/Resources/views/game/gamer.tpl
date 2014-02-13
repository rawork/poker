{if !$game->isState(0)}<div class="gamer" id="gamer">
	<div class="gamer-cards" id="gamer-cards">
		{foreach from=$gamer->getCards() key=k item=card}
		<div class="card{if $game->isState(1)} choose{/if}{if !$gamer->isWinner() && ($game->isState(2) || $game->isState(3) || $game->isState(4)) && $gamer->isCombination($card.name)} hint{/if}{if $game->isState(4) && $game->isCombination($card.name)} active{/if}" data-card-name="{$card.name}" data-card-id="{$k}"><img src="/bundles/public/img/cards/{$card.name}.png" /></div> 
		{foreachelse}
		{if $game->isState(2) || $game->isState(3) || $game->isState(4)}<div class="pass">ПАС</div>{/if}
		{/foreach}
	</div>
	<div class="gamer-title-container">
		<div class="gamer-title">
			<div class="gamer-avatar pull-left"><img src="{$gamer->getAvatar()}" /></div>
			<div class="gamer-status gamer-status-ready">Активен</div>
			<div class="gamer-name">{$gamer->getName()}<br>{$gamer->getLastname()}</div>
		</div>
		<div class="gamer-info">
			<div><img src="/bundles/public/img/chips.png"> Фишки: <span class="gamer-chips" id="chips">{$gamer->getChips()}</span></div>
			<div><img src="/bundles/public/img/bet.png"> Ставка: <span class="gamer-bet" id="bet">{$gamer->getBet()}</span> </div>
		</div>
		<div class="clearfix"></div>
	</div>	
</div>
<div class="game-buttons-container">
	<div class="game-arrow"><img src="/bundles/public/img/arrow.png" /></div>
	<div class="game-buttons">
		Введите сумму:
		<input type="text" id="input_bet" value="{$game->minbet}">
		<button class="btn btn-warning" data-action="allin">Ва-банк</button>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<button class="btn btn-success" data-action="bet">Ставка</button>
		<button class="btn btn-primary" data-action="check">Ответить</button>
		<button class="btn btn-danger" data-action="fold">Пас</button>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<button class="btn btn-primary btn-buy" data-action="buy">Покупка фишек</button>
		<a class="btn btn-warning" data-action="out">{if $gamer->isState(3)}В игре{else}Вне игры{/if}</a>
	</div>
</div>{/if}