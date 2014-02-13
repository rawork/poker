{if $game->isState(4)}
{foreach from=$game->getWinner() item=winner}
	<div class="game-winner winner{$gamer->getRivalPosition($winner.seat, $winner.numOfGamers)}">Победитель &laquo;{$winner.name}&raquo;</div>
{/foreach}
{/if}