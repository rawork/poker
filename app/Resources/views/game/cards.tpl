{if $gamer->getCards()}
{foreach from=$gamer->getCards() key=k item=card}
{if is_array($card)}
<div class="card{if $game->isState(1)} choose{/if}{if !$gamer->isWinner() && ($game->isState(2) || $game->isState(3) || $game->isState(4)) && $gamer->isCombination($card.name)} hint{/if}{if $game->isState(4) && $game->isCombination($card.name)} active{/if}" data-card-name="{$card.name}" data-card-id="{$k}"><img src="/bundles/public/img/cards/{$card.name}.png" /></div> 
{/if}
{foreachelse}
{if $game->isState(2) || $game->isState(3) || $game->isState(4)}<div class="pass">ПАС</div>{/if}
{/foreach}
<div class="clearfix"></div>
{/if}