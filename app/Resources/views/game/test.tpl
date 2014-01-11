<div>
	{foreach from=$suite item=card}
	<img src="/bundles/public/img/cards/{$card.name}.png">
	{/foreach}
</div>
<h4>{$rank} - {$cards.weight}</h4>
<div>
	{if is_array($cards)}
	{foreach from=$cards.cards item=card}
	<img src="/bundles/public/img/cards/{$card.name}.png">
	{/foreach}
	{else}
	{$cards}
	{/if}
</div>