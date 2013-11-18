<!DOCTYPE html>
<html>
	<head>
		<title>{$title}</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		{$meta}
		<link rel="stylesheet" href="{$theme_ref}/bootstrap3/css/bootstrap.css" type="text/css" media="screen">
		<link rel="stylesheet" href="{$theme_ref}/bootstrap3/css/bootstrap-theme.css" type="text/css" media="screen">
		<link rel="stylesheet" href="{$theme_ref}/lightbox/css/lightbox.css" type="text/css" media="screen">
		<link rel="stylesheet" href="{$theme_ref}/public/css/default.css" type="text/css" media="screen">
		<!--[if lt IE 9]>
		{*<script type="text/javascript" src="{$theme_ref}/ie/html5shiv.js"></script>*}
		<script type="text/javascript" src="{$theme_ref}/ie/respond.min.js"></script>
		<![endif]-->
		<script type="text/javascript">
		var prj_ref = '{$prj_ref}';
		</script>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="logo"><a href="{raURL node=/}"><img src="{$theme_ref}/public/img/logo_{$locale}.png"></a></div>
			</div>
			<div class="row">
				<div class="col-md-2">
					<ul class="mainmenu">
						<li><a href="{raURL node=/}">{"Home"|t}</a></li>
						{foreach from=$links item=menuitem}
						<li{if $menuitem.id == $curnode.id} class="active"{/if}><a href="{$menuitem.ref}">{$menuitem.title}</a></li>
						{/foreach}
						<li><a href="/">{"Ancor main site"|t}</a></li>
						{if $locale != 'ru'}
						<li>&nbsp;</li>
						<li><a href="http://ancor.ru{raURL node=/}">RU</a></li>
						{/if}
					</ul>
				</div>
				<div class="col-md-{if $colnum}{$colnum}{else}10{/if}">
					<div class="content">
						<h1>{$h1}</h1>
						{eval var=$mainbody}
					</div>
				</div>
				{if $gifts}	
				<div class="col-md-4">
					<div class="well1">
						<div class="add-gift"><a class="btn btn-default" href="javascript:void(0)" onclick="showAddDialog()"><img class="pull-left" src="{$theme_ref}/public/img/stars-small.png" alt="" /> {"Add<br>useful<br>thing"|t}</a></div>
						{raMethod path=Fuga:Public:Common:block args='["name":"gift_text"]'}
					</div>
				</div>
				{/if}
				{if $advice}	
				<div class="col-md-4">
					<div class="well1">
						<div class="comeback"><a class="btn btn-default" href="{raURL node=$curnode.name}"><img class="pull-left" src="{$theme_ref}/public/img/stars-small.png" alt="" /> {"Back to catalog beneficiaries"|t}</a></div>
						{raMethod path=Fuga:Public:Common:block args='["name":"advice_text"]'}
					</div>
				</div>
				{/if}
			</div>
		</div>
		<div id="advice"><a class="btn btn-default" href="{if $locale == 'ru'}{raURL node=russia}{else}{raURL node=ukraine}{/if}"><img class="pull-left" src="{$theme_ref}/public/img/man-small.png"> <span>{"main_button"|t}</span></a></div>
		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
			  <div class="modal-content">
				<div class="modal-header"></div>
				<div class="modal-body"></div>
				<div class="modal-footer"></div>
			  </div>
			</div>
		</div>
		<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
			  <div class="modal-content">
				<div class="modal-header"><a class="close" href="{raURL node=/}" aria-hidden="true">&times;</a></div>
				<div class="modal-body"></div>
			  </div>
			</div>
		</div>
		<iframe id="upload_target" name="upload_target" src="#"></iframe>
		<script type="text/javascript" src="{$theme_ref}/jquery/jquery.js"></script>
		<script type="text/javascript" src="{$theme_ref}/bootstrap3/js/bootstrap.js"></script>
		<script type="text/javascript" src="{$theme_ref}/lightbox/js/lightbox-2.6.min.js"></script>
		<script type="text/javascript" src="{$theme_ref}/public/js/public.js"></script>
		<script type="text/javascript">
		{if $javascript}
		$('#myModal2 .modal-content').addClass('blue-body');
		$('#myModal2 .modal-body').html('{'Thanks again'|t}');
		$('#myModal2').modal('show');
		{/if}
		</script>
	</body>
</html>
