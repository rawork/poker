<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Управление сайтом - {$prj_name}.{$currentLocale}</title>
	<link href="{$theme_ref}/bootstrap3/css/bootstrap.min.css" type="text/css" rel="stylesheet">
	<link href="{$theme_ref}/bootstrap3/css/bootstrap-theme.min.css" type="text/css" rel="stylesheet">
	<link href="{$theme_ref}/admin/css/default.css" type="text/css" rel="stylesheet">
	<link href="{$theme_ref}/admin/css/colorpicker.css" type="text/css" rel="stylesheet">
	<link href="{$theme_ref}/calendar/calendar.css" type="text/css" rel="stylesheet">
	<link href="{$theme_ref}/treeview/jquery.treeview.css" type="text/css" rel="stylesheet">
	<script type="text/javascript">
	var state = '{$state}';
	var theme_ref = '{$theme_ref}';
	var prj_ref = '{$prj_ref}';
	{literal}
	var calendars = {};
	function addCalendar(name, time) {
		calendars[name] = time;
	}
	{/literal}
	</script>
</head>
<body>
	<nav class="navbar navbar-default navbar-fixed-top navbar-inverse" role="navigation">
		<div class="navbar-header">
		  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
			<span class="sr-only">Управление</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		  </button>
		  <a class="navbar-brand" target="_blank" href="{raURL node=/}">{$prj_name}.{$currentLocale}</a>
		</div>

		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		  <ul class="nav navbar-nav">

			<li class="divider"></li>
			{foreach from=$states key=stateName item=stateTitle}  
			<li{if state == stateName} class="active"{/if}><a href="javascript:getComponentList('{$stateName}', '{$module}')">{$stateTitle}</a></li>
			{*<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown">{$stateTitle} <b class="caret"></b></a>
				<ul class="dropdown-menu">

					<li class="nav-header">Nav header</li>
					<li><a href="#">Action</a></li>
					<li><a href="#">Another action</a></li>
					<li><a href="#">Something else here</a></li>
					<li class="divider"></li>

				</ul>
			</li>*}
			<li class="divider"></li>
			{/foreach}
			{foreach from=$locales item=locale}
			<li>
				{if $locale.name == $currentLocale}
				<a class="locale" href="javascript:void(0)"><span class="label label-info">{$locale.name}</span></a>
				{else}
				<a class="locale" href="javascript:void(0)" onclick="setLocale('{$locale.name}')"><span class="label pointer-view" >{$locale.name}</span></a>
				{/if}
			</li>
			{/foreach}
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li><a href="{raURL node=admin method=logout}" title="Выйти из Управление сайтом"><span class="glyphicon glyphicon-user"></span> {$user.name} {$user.lastname}, выйти</a></li>
			</ul>
		</div><!-- /.navbar-collapse -->
	</nav>
	<div class="container">
	<div class="row">
		<div class="col-md-3">
			<div class="well" id="moduleMenu">{include file='admin/mainmenu.tpl'}</div>
			<form id="formLocale" method="post"><input type="hidden" name="locale" value="ru"></form>
		</div>
		<div class="col-md-9">
			{if $module eq ''}
				<table>
				<tr>
				<td> {foreach from=$modules item=mod}
				<div style="float: left;padding: 5px">
					<table cellspacing="0">
					<tr>
						<td align="center"><a href="{$mod.ref}"><img border="0" src="{$mod.icon}"></a></td>
					</tr>
					<tr>
						<td align="center"><a href="{$mod.ref}">{$mod.title}</a></td>
					</tr>
					</table>
				</div>
				{/foreach} </td>
				</tr>
				</table>
			{else}
			{$content}
			{/if}
		</div>
	</div>
	</div>
	<div class="navbar navbar-default navbar-fixed-bottom footer">Fuga CMS &copy; 2005-2013</div>	
	<div id="waiting" class="hidden"><img src="{$theme_ref}/admin/img/loading.gif">Обработка запроса...</div>
	<div class="modal fade" id="modalDialog" tabindex="-1" role="dialog" aria-labelledby="popupTitle" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="popupTitle"></h4>
				</div>
				<div class="modal-body"id="popupContent"></div>
				<div class="modal-footer admin-modal-footer" id="popupButtons"></div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<script type="text/javascript" src="{$theme_ref}/jquery/jquery.js"></script>
    <script type="text/javascript" src="{$theme_ref}/bootstrap3/js/bootstrap.js"></script>
	<script type="text/javascript" src="{$theme_ref}/treeview/lib/jquery.cookie.js"></script>
	<script type="text/javascript" src="{$theme_ref}/treeview/jquery.treeview.js"></script>
	<script type="text/javascript" src="{$theme_ref}/multifile/jquery.MultiFile.js"></script>
	<script type="text/javascript" src="{$theme_ref}/multifile/jquery.form.js"></script>
	<script type="text/javascript" src="{$theme_ref}/multifile/jquery.blockUI.js"></script>
	<script type="text/javascript" src="{$theme_ref}/admin/js/jquery.colorpicker.js"></script>
	<script type="text/javascript" src="{$theme_ref}/admin/js/admin.js"></script>
	<script type="text/javascript" src="{$theme_ref}/tinymce/tinymce.min.js"></script> 
	<script type="text/javascript" src="{$theme_ref}/tinymce/tinymce_init.js"></script>
	<script type="text/javascript" src="{$theme_ref}/calendar/calendar.js"></script>
	<script type="text/javascript" src="{$theme_ref}/calendar/calendar-ru.js"></script>
	<script type="text/javascript" src="{$theme_ref}/calendar/calendar-setup.js"></script>
</body>
</html>