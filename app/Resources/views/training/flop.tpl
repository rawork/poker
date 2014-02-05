<div class="game-flop">	
	{foreach from=$training->flop item=card}
	<div class="card{if $training->isState(4) && $training->combination[$card.name]} active{/if}" data-card-name="{$card.name}">
		{if $training->isState(3) || $training->isState(4)}<img src="/bundles/public/img/cards/{$card.name}.png" />
		{else}
		<img src="/bundles/public/img/shirt.png" />
		{/if}
	</div>
	{/foreach}
</div>
<div class="game-timer" id="game-timer"></div>