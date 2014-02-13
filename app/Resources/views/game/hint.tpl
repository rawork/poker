{if ($game->isState(2) || $game->isState(3) || $game->isState(4)) && $gamer->getRank()}
<div class="gamer-hint hint0">{if $gamer->getCards()}&laquo;{$gamer->getRank()}&raquo;{/if}</div>
{/if}