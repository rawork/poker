<div class="row">
{foreach from=$gamers item=gamer key=k}
	<div class="col-md-6">
		<div class="well"><h4>Игрок {$k+1}</h4>
			<div>Банк: &dollar;{$gamer.money} Ставка &dollar;<span id="bet{$k+1}">20</span> </div>
			<div>Комбинация: {$gamer.rank}</div>
			<div>{foreach from=$gamer.hand  item=card}
				<img src="/bundles/public/img/{$card.name}.png" /> 
				{/foreach}
			</div>	
			<br>
			<div class="row">
				<div class="col-xs-3">
				<input type="text" class="form-control" name="bet">
				</div>
				<div class="col-xs-9">
				<input class="btn btn-primary" type="button" value="Ставка">
				<input class="btn btn-success" type="button" value="Чек">
				<input class="btn btn-danger" type="button" value="Пас">
				</div>
			</div>
		</div>
	</div>
{if $k == 1}
</div>
<div class=row">{/if}
{/foreach}
</div>
<div class="container">
	<div class="well"><h4>Банк Всего: &dollar;800  Ставки: &dollar;100</h4>
	<div>
	{foreach from=$flop item=card}
		<img src="/bundles/public/img/{$card.name}.png" />
	{/foreach}
	</div>
</div>
<input type="button" class="btn btn-success btn-lg" onclick="window.location.reload()" value="Новая партия" /></div>	
