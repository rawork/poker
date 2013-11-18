<div id="filter">
<form method="post" id="frmFilter" action="{$baseRef}">
<input type="hidden" id="filter_type" name="filter_type" value="set" />
<h6>Фильтры</h6>
<table class="table table-condensed">
<thead>
<tr>
<th></th>
<th></th>
</tr>
</thead>
<tr>
	<td align="left" width="100">Id</td>
	<td><input class="form-control" type="text" name="search_filter_id" value="{$smarty.request.search_filter_id}"></td>
</tr>
{foreach from=$fields item=field}
{if $field.search}
<tr>
	<td align="left" width="100">{$field.title}</td>
	<td>
		{$field.searchinput}
	</td>
</tr>
{/if}
{/foreach}
</table>
<div class="btn-group">
	<a class="btn btn-default" onclick="preFilter('set');">Искать</a>
	<a class="btn btn-default" onclick="preFilter('cansel');">Отменить фильтры</a>
</div>	
</form> 
</div>