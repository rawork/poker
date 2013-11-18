<ul class="breadcrumb">
	{foreach from=$nodes key=k item=item}	
	{if $k lt count($nodes)-1}
	<li><a href="{$item.ref}">{$item.title}</a>{if $k lt count($nodes)-1} <span class="divider">&rarr;</span>{/if}</li>
	{else}	
	{*<li class="active">{$item.title}</li>*}
	{/if}
	{/foreach}
</ul>