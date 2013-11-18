<ul class="pagination pagination-sm">
	<li{if $prev_link == ''} class="disabled"{/if}><a href="{$prev_link}">&laquo;</a></li>
	{foreach from=$pages item=i}
	<li{if $page == $i.name} class="active"{/if}><a href="{$i.ref}">{$i.name}</a></li>
	{/foreach}
	<li{if $next_link == ''} class="disabled"{/if}><a href="{$next_link}">&raquo;</a></li>
</ul>
