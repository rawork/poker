<div>
	{foreach from=$suite item=card}
	<img style="height:150px;" src="/bundles/public/img/cards/{$card.name}.png">
	{/foreach}
</div>
<h2>{$rank} - {$cards.weight}</h2>
<div>
	{if is_array($cards)}
	{foreach from=$cards.cards item=card}
	<img style="height:150px;" src="/bundles/public/img/cards/{$card.name}.png">
	{/foreach}
	{else}
	{$cards}
	{/if}
</div>