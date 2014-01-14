<!DOCTYPE html>
<html>
	<head>
		<title>{$title}</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		{$meta}
		<link rel="stylesheet" href="{$theme_ref}/bootstrap2/css/bootstrap.css" type="text/css" media="screen">
		<link rel="stylesheet" href="{$theme_ref}/public/css/default.css" type="text/css" media="screen">
		<link rel="stylesheet" href="{$theme_ref}/public/css/game.css" type="text/css" media="screen">
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
			{$maincontent}
		</div>
		<div class="poker-line"></div>
		<div class="container">
			<div class="row-fluid">
				<div class="span5">
					<ul class="footer-menu">
						{foreach from=$links item=link name=link}
						<li>{if $smarty.foreach.link.index > 0} / {/if}<a href="{$link.ref}">{$link.title}</a></li>
						{/foreach}
					</ul>
				</div>
				<div class="span2 text-center">
					<img class="footer-logo" src="{$theme_ref}/public/img/logo.png">
				</div>
				<div class="span5 footer-right">
					<ul class="login-menu">
						<li><a href="/account">АВТОРИЗАЦИЯ</a> /</li>
						<li><a href="/account/register">РЕГИСТРАЦИЯ</a></li>
					</ul>
				</div>
			</div>
		</div>
		<script type="text/javascript" src="{$theme_ref}/jquery/jquery.js"></script>
		<script type="text/javascript" src="{$theme_ref}/jquery/jquery.cookie.js"></script>
		<script type="text/javascript" src="{$theme_ref}/bootstrap2/js/bootstrap.js"></script>
		<script type="text/javascript" src="{$theme_ref}/public/js/public.js"></script>
	</body>
</html>
