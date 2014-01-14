<div class="row-fluid">
	{foreach from=$prizes item=prize}
	<div class="span4 prize">
		<img src="{$prize.foto_value.value}">
		<div class="title {$prize.color}">{$prize.name}</div>
		<div class="description">{$prize.description}</div>
	</div>
	{/foreach}
</div>