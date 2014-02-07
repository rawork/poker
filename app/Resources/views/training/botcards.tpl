{foreach from=$gamer->cards item=card}
<div class="card{if $training->isState(4) && $training->combination[$card.name]} active{/if}" data-card-name="{$card.name}">
	{if $training->isState(4)}<img src="/bundles/public/img/cards/{$card.name}.png" />
	{else}
	<img src="/bundles/public/img/shirt.png" />
	{/if}
</div> 
{foreachelse}
{if $training->isState(2) || $training->isState(3) || $training->isState(4)}<div class="pass">ПАС</div>{/if}
{/foreach}