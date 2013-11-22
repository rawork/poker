<!DOCTYPE html>
<html>
	<head>
		<title>{$title}</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		{$meta}
		<link rel="stylesheet" href="{$theme_ref}/bootstrap3/css/bootstrap.css" type="text/css" media="screen">
		<link rel="stylesheet" href="{$theme_ref}/bootstrap3/css/bootstrap-theme.css" type="text/css" media="screen">
		{*<link rel="stylesheet" href="{$theme_ref}/public/css/default.css" type="text/css" media="screen">*}
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
				<div class="col-md-1">
				</div>
				<div class="col-md-10">
					<div class="content">
						<h1>{$h1}</h1>
						{eval var=$mainbody}
					</div>
				</div>
				<div class="col-md-1">
				</div>
			</div>
		</div>
		<script type="text/javascript" src="{$theme_ref}/jquery/jquery.js"></script>
		<script type="text/javascript" src="{$theme_ref}/bootstrap3/js/bootstrap.js"></script>
		<script type="text/javascript" src="{$theme_ref}/public/js/public.js"></script>
	</body>
</html>
