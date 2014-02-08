{foreach from=$training->gamer->cards key=k item=card}
<div class="card{if $training->isState(1)} choose{/if}{if ($training->isState(2) || $training->isState(3) || $training->isState(4)) && $training->gamer->combination[$card.name]} hint{/if}{if $training->isState(4) && $training->combination[$card.name]} active{/if}" data-card-name="{$card.name}" data-card-id="{$k}"><img src="/bundles/public/img/cards/{$card.name}.png" /></div> 
{foreachelse}
{if $training->isState(2) || $training->isState(3) || $training->isState(4)}<div class="pass">ПАС</div>{/if}
{/foreach}
<div class="clearfix"></div>