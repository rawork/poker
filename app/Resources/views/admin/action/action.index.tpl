<br>
{$paginator->render()}
<form id="frmGroupUpdate" name="frmGroupUpdate" action="{$baseRef}/groupedit" method="post">
<input type="hidden" name="edited" value="1">
<table class="table table-condensed table-normal">
<thead>
<tr>
<th width="1%"><input type="checkbox" id="list-checker"></th>
<th width="1%">#</th>
{foreach from=$fields item=field}
	{if $field.width}
	<th width="{$field.width}">{$field.title}</th>
	{/if}
{/foreach}
{if $showCredate}
<th width="10%">Дата создания</th>
{/if}
<th style="text-align:center"><span class="glyphicon glyphicon-align-justify"></span></th>
</tr>
</thead>
{$tableData}
</table>
<div class="form-inline" id="control">
	<div class="form-group">
	{if $showGroupSubmit}
		<a class="btn btn-sm btn-default" title="Сохранить" onclick="startGroupUpdate(false)"><span class="glyphicon glyphicon-floppy-disk"></span></a>
		{/if}
		<a class="btn btn-sm btn-default" title="Изменить" onclick="startGroupUpdate(true)"><span class="glyphicon glyphicon-pencil"></span></a>
		<a class="btn btn-sm btn-danger" title="Удалить" onclick="startGroupDelete()"><span class="glyphicon glyphicon-trash icon-white"></span></a>
	</div>
	{if $rpps}	
	<div class="form-group rpp">
		<div class="input-group">
			<span class="input-group-addon">&nbsp;&nbsp;На странице:&nbsp;&nbsp;</span> 
			<select class="form-control col-sm-3" name="rpp" onChange="updateRpp(this, '{$tableName}')">';
			{foreach from=$rpps item=rpp}
			<option value="{$rpp}" {if $rowPerPage == $rpp} selected{/if}>{$rpp}</option>
			{/foreach}
			</select>
	
		</div>
	</div>		
	{/if}
</div>
<input type="hidden" name="ids" id="ids" value="{$ids}"></form>
{$paginator->render()}