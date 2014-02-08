{if ($training->isState(2) || $training->isState(3) || $training->isState(4)) && $training->gamer->rank}
<div class="gamer-hint hint0">{if $training->gamer->cards}&laquo;{$training->gamer->rank}&raquo;{/if}</div>
{/if}