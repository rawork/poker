{foreach from=$tables item=table}
	<div><a href="{$table.ref}"><span class="glyphicon glyphicon-list-alt"></span> {$table.name}</a></div>
{/foreach}
