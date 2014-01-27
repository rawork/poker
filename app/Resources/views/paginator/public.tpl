<div class="pagination">
	Страницы: 
	{if $begin_link != ''}<a href="{$begin_link}">&laquo;</a>{/if}
	{if $prev_link != ''}<a href="{$prev_link}">&lsaquo;</a>{/if} 
	{foreach from=$pages item=i}
	<a{if $page == $i.name} class="active"{/if} href="{$i.ref}">{$i.name}</a>
	{/foreach}
	{if $next_link != ''}<a href="{$next_link}">&rsaquo;</a>{/if}
	{if $end_link != ''}<a href="{$end_link}">&raquo;</a>{/if} 
</div>
