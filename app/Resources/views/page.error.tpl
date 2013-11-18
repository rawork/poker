<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="{$theme_ref}/public/css/error.css" type="text/css">
	<link href="/favicon.ico" rel="icon" type="image/x-icon" />
	<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <title>{$status_code}</title>
</head>
<body>
    <div class="error">
		<div><a href="/"><img src="{$theme_ref}/public/img/logo_{$locale}.png"></a></div>
		<br>
		<h1>{$status_code}</h1>
		<div class="error-text">{$status_text}.<br><a href="{$prj_ref}/">Перейти на главную страницу</a></div>
	</div>
</body>
</html>