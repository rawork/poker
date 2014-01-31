<div class="game-flop">	
	{foreach from=$training->board->flop item=card}
	<div class="card{if $training->board->state == 4 && $training->board->combination[$card.name]} active{/if}" data-card-name="{$card.name}">
		{if $training->board->state == 3 || $training->board->state == 4}<img src="/bundles/public/img/cards/{$card.name}.png" />
		{else}
		<img src="/bundles/public/img/shirt.png" />
		{/if}
	</div>
	{/foreach}
</div>
<div class="game-timer" id="game-timer"></div>