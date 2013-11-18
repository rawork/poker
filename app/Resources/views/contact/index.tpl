<p></p>
{foreach from=$items item=item}
<div class="row">
	<div class="col-md-2 col-md-offset-1">
		<img src="{$item.logo_value.value}" class="img-margin-top">
	</div>
	<div class="col-md-9">
		<div class="partner">{$item.name}</div>
		<div class="site">{$item.site}</div>
		<div>{$item.address}</div>
		<br><br>
	</div>
</div>
{/foreach}
