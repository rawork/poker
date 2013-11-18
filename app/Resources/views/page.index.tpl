<!DOCTYPE html>
<html>
	<head>
		<title>АНКОР. {$title}</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		{$meta}
		<link rel="stylesheet" href="{$theme_ref}/bootstrap3/css/bootstrap.css" type="text/css" media="screen">
		<link rel="stylesheet" href="{$theme_ref}/bootstrap3/css/bootstrap-theme.css" type="text/css" media="screen">
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
				<div class="logo"><img src="{$theme_ref}/public/img/logo_{$locale}.png"></div>
			</div>
			<div class="row">
				<div class="col-md-2">
					<ul class="mainmenu">
						<li class="active"><a href="{raURL node=/}">{"Home"|t}</a></li>
						{foreach from=$links item=menuitem}
						<li><a href="{$menuitem.ref}">{$menuitem.title}</a></li>
						{/foreach}
						<li><a href="/">{"Ancor main site"|t}</a></li>
						{if $locale != 'ru'}
						<li>&nbsp;</li>
						<li><a href="http://ancor.ru{raURL node=/}">RU</a></li>
						{/if}
					</ul>
				</div>
				<div class="col-md-6">
					<div class="content">
						<div class="index-foto"><img src="{$theme_ref}/public/img/girl.jpg"></div>
						<h1>{$h1}</h1>
						{$mainbody}
					</div>
				</div>
				<div class="col-md-4">
					<div class="well1">
						{raMethod path=Fuga:Public:Common:block args='["name":"mainpage_text"]'}
					</div>
					<div class="well2">
						{"I have a question / suggestion organizing committee shares"|t}
						<form method="post" id="messageForm">
						<div><input class="form-control input-sm" type="text" name="email" placeholder="E-mail"></div><br>	
						<div><textarea class="form-control" name="message" rows="3"></textarea></div>
						<div><input class="btn btn-default btn-sm" onclick="sendMessage()" value="{"Send"|t}"></div>
						</form>
						<br>
						<div id="messageResult" class="label"></div>
					</div>
				</div>
			</div>
		</div>
		<div id="advice"><a class="btn btn-default" href="{if $locale == 'ru'}{raURL node=russia}{else}{raURL node=ukraine}{/if}"><img class="pull-left" src="{$theme_ref}/public/img/man-small.png"> <span>{"main_button"|t}</span></a></div>
		<script type="text/javascript" src="{$theme_ref}/jquery/jquery.js"></script>
		<script type="text/javascript" src="{$theme_ref}/public/js/public.js"></script>
	</body>
</html>
