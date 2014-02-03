{foreach from=$training->gamer->cards key=k item=card}
<div class="card{if $training->isState(1)} choose{/if}{if $training->isState(4) && $training->board->combination[$card.name]} active{/if}" data-card-name="{$card.name}" data-card-id="{$k}"><img src="/bundles/public/img/cards/{$card.name}.png" /></div> 
{/foreach}
<div class="clearfix"></div>