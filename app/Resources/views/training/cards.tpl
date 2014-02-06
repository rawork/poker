{foreach from=$training->gamer->cards key=k item=card}
<div class="card{if $training->isState(1)} choose{/if}{if $training->isState(4) && $training->combination[$card.name]} active{/if}{if ($training->isState(2) || $training->isState(3)) && $training->gamer->combination[$card.name]} hint{/if}" data-card-name="{$card.name}" data-card-id="{$k}"><img src="/bundles/public/img/cards/{$card.name}.png" /></div> 
{/foreach}
{foreach from=$training->gamer->comination key=name item=card}
{$name}
{/foreach}
<div class="clearfix"></div>