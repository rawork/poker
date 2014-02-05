{if !$training->isState(0) && !$training->isState(6)}
{foreach from=$training->bots item=gamer}
<div class="gamer-bot gamer{$gamer->position}" data-bot-id="{$gamer->id}">
	{if $gamer->position == 1 || $gamer->position == 5}
	<div class="gamer-cards" data-bot-id="{$gamer->id}">
		{foreach from=$gamer->cards item=card}
		<div class="card{if $training->isState(4) && $training->combination[$card.name]} active{/if}" data-card-name="{$card.name}">
			{if $training->isState(4)}<img src="/bundles/public/img/cards/{$card.name}.png" />
			{else}
			<img src="/bundles/public/img/shirt.png" />
			{/if}
		</div> 
		{/foreach}
	</div>
	{/if}
	<div>
		<div class="gamer-title">
			<div class="pull-left gamer-avatar"><img src="{$gamer->avatar}" /></div>
			{if $gamer->isActive()}
			<div class="gamer-status" data-bot-id="{$gamer->id}">Активен</div>
			{else}
			<div class="gamer-status notready" data-bot-id="{$gamer->id}">Не активен</div>
			{/if}
			<div class="gamer-name">{$gamer->name}<br>{$gamer->lastname}</div>
		</div>
		<div class="gamer-info">
			<div><img src="/bundles/public/img/chips.png"> Фишки: <span class="gamer-chips" data-bot-id="{$gamer->id}">{$gamer->chips}</span></div>
			<div><img src="/bundles/public/img/bet.png"> Ставка: <span class="gamer-bet" data-bot-id="{$gamer->id}">{$gamer->bet}</span> </div>
		</div>
	</div>	
	{if $gamer->position > 1 && $gamer->position < 5}
	<div class="gamer-cards" data-bot-id="{$gamer->id}">
		{foreach from=$gamer->cards item=card}
		<div class="card{if $training->isState(4) && $training->combination[$card.name]} active{/if}" data-card-name="{$card.name}">
			{if $training->isState(4)}<img src="/bundles/public/img/cards/{$card.name}.png" />
			{else}
			<img src="/bundles/public/img/shirt.png" />
			{/if}
		</div> 
		{/foreach}
	</div>
	{/if}
</div>
{/foreach}
{/if}