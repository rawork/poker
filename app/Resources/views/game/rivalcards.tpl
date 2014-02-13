{foreach from=$rival->cards item=card}
<div class="card{if $game->isState(4) && $game->isCombination($card.name)} active{/if}" data-card-name="{$card.name}">
	{if $game->isState(4)}<img src="/bundles/public/img/cards/{$card.name}.png" />
	{else}
	<img src="/bundles/public/img/shirt.png" />
	{/if}
</div> 
{foreachelse}
{if $rival->isActive() && ($game->isState(2) || $game->isState(3) || $game->isState(4))}<div class="pass">ПАС</div>{/if}
{/foreach}