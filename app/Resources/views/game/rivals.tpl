{foreach from=$rivals item=rival}
<div class="gamer-bot gamer{$rival->position}" data-bot-id="{$rival->id}">
	{if $rival->position == 1 || $rival->position == 5}
	<div class="gamer-cards" data-bot-id="{$rival->id}">
		{foreach from=$rival->cards item=card}
		<div class="card{if $game->isState(4) && $game->isCombination($card.name)} active{/if}">
			{if $game->isState(4)}<img src="/bundles/public/img/cards/{$card.name}.png" />
			{else}
			<img src="/bundles/public/img/shirt.png" />
			{/if}
		</div>
		{foreachelse}
		{if $rival->isActive() && ($game->isState(2) || $game->isState(3) || $game->isState(4))}<div class="pass">ПАС</div>{/if}
		{/foreach}
	</div>
	{/if}
	<div>
		<div class="gamer-title">
			<div class="pull-left gamer-avatar"><img src="{$rival->avatar}" /></div>
			{if $rival->isHere()}
			<div class="gamer-status" data-bot-id="{$rival->id}">Активен</div>
			{else}
			<div class="gamer-status notready" data-bot-id="{$rival->id}">{if $rival->state == 3}Вне игры{else}Не активен{/if}</div>
			{/if}
			<div class="gamer-name">{$rival->name}<br>{$rival->lastname}</div>
		</div>
		<div class="gamer-info">
			<div><img src="/bundles/public/img/chips.png"> Фишки: <span class="gamer-chips" data-bot-id="{$rival->id}">{$rival->chips}</span></div>
			<div><img src="/bundles/public/img/bet.png"> Ставка: <span class="gamer-bet" data-bot-id="{$rival->id}">{$rival->bet}</span> </div>
		</div>
	</div>	
	{if $rival->position > 1 && $rival->position < 5}
	<div class="gamer-cards" data-bot-id="{$rival->id}">
		{foreach from=$rival->cards item=card}
		<div class="card{if $game->isState(4) && $game->isCombination($card.name)} active{/if}">
			{if $game->isState(4)}<img src="/bundles/public/img/cards/{$card.name}.png" />
			{else}
			<img src="/bundles/public/img/shirt.png" />
			{/if}
		</div> 
		{foreachelse}
		{if $rival->isActive() && ($game->isState(2) || $game->isState(3) || $game->isState(4))}<div class="pass">ПАС</div>{/if}
		{/foreach}
	</div>
	{/if}
</div>
{/foreach}
