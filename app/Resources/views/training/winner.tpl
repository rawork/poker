{if $training->isState(4)}
{foreach from=$training->winner item=combination}
	<div class="game-winner winner{$combination.position}">Победитель &laquo;{$combination.name}&raquo;</div>
{/foreach}
{/if}