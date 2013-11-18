<div class="row">
	{foreach from=$items item=item}
	<div class="col-md-{$quantity}">
		<div class="well1">
			<img src="{$item.logo_value.value}">
			<div class="partner">{$item.name}</div>
			<div class="site">{$item.site}</div>
			{$item.description}
		</div>
	</div>
	{/foreach}
</div>