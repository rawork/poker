{if $training->board->state == 4}
{foreach from=$training->board->winner item=combination}
<div class="game-winner winner{$combination.position}">Победитель - {$combination.name}</div>
{/foreach}
{/if}