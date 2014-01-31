{foreach from=$training->bots item=gamer}
<div class="gamer{$gamer->position}">
	{if $gamer->position == 1 || $gamer->position == 5}
	<div class="gamer-cards">
		{foreach from=$gamer->cards item=card}
		<div class="card{if $training->board->state == 4 && $training->board->combination[$card.name]} active{/if}" data-card-name="{$card.name}">
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
		<div class="card{if $training->board->state == 4 && $training->board->combination[$card.name]} active{/if}" data-card-name="{$card.name}">
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