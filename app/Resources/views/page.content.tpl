<!DOCTYPE html>
<html>
	<head>
		<title>{$title}</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		{$meta}
		<link rel="stylesheet" href="{$theme_ref}/bootstrap2/css/bootstrap.css" type="text/css" media="screen">
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
			<div class="row-fluid">
				<div class="span2"><a href="/"><img class="logo" src="/bundles/public/img/logo.png"></a></div>
				<div class="span6">
					<ul class="mainmenu">
						{foreach from=$links item=link name=link}
						<li class="item{$smarty.foreach.link.index+1}"><a{if $curnode.id == $link.id} class="active"{/if} href="{$link.ref}">{$link.title}</a></li>
						{/foreach}
					</ul>
					<div class="clearfix"></div>
				</div>
				<div class="span4 header-right">
					{raMethod path=Fuga:Public:Account:widget}
				</div>
			</div>
		</div>
		<div class="poker-line"></div>
		<div class="container">
			<h1>{$h1}</h1>
			{$maincontent}
		</div>
		{if 1 == 2}
		<div class="splash">
			<div class="container">
				<div class="index-buttons">
					<a class="btn btn-warning btn-large" href="/game">Игровой<small>зал</small></a>
					<a class="btn btn-success btn-large" href="/training">Тренировочный<small>зал</small></a>
				</div>
			</div>
		</div>
		{/if}
		<div class="container gamers-container">
			{if $curnode.name == 'rules'}<hr class="red-line">
			{raMethod path=Fuga:Public:News:lenta}{/if}
			{if $curnode.name == 'prizes' || $curnode.name == 'rules'}
			{raMethod path=Fuga:Public:Account:members}
			{/if}
		</div>
		{if $curnode.name == 'account'}
		<div class="splash">
			<div class="container">
				<div class="index-buttons">
					<a class="btn btn-warning btn-large" href="/game">Игровой<small>зал</small></a>
					<a class="btn btn-success btn-large" href="/training">Тренировочный<small>зал</small></a>
				</div>
			</div>
		</div>	
		{/if}
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
					<a href="/"><img class="footer-logo" src="{$theme_ref}/public/img/logo.png"></a>
				</div>
				<div class="span5 footer-right">
					{raMethod path=Fuga:Public:Account:widget}
				</div>
			</div>
		</div>
		<script type="text/javascript" src="{$theme_ref}/jquery/jquery.js"></script>
		<script type="text/javascript" src="{$theme_ref}/jquery/jquery.idle.js"></script>
		<script type="text/javascript" src="{$theme_ref}/bootstrap2/js/bootstrap.js"></script>
		<script type="text/javascript" src="{$theme_ref}/public/js/{$javascript}.js"></script>
	</body>
</html>
