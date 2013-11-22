<div class="row">
{foreach from=$gamers item=gamer key=k}
	<div class="col-md-6">
		<div class="well"><h4>Игрок {$k+1}</h4>
			<div>Комбинация: {$gamer.rank}</div>
			<div>{foreach from=$gamer.suite  item=card}
				<img src="/bundles/public/img/{$card.name}.png" /> 
				{/foreach}
			</div>	
		</div>
	</div>
{if $k == 1}
</div>
<div class=row">{/if}
{/foreach}
</div>
<div class="container">
<div class="well"><h4>Флоп</h4>
	<div>
	{foreach from=$flop item=card}
		<img src="/bundles/public/img/{$card.name}.png" />
	{/foreach}
	</div>
</div>
<input type="button" class="btn btn-success btn-lg" onclick="window.location.reload()" value="Новая партия" /></div>	
