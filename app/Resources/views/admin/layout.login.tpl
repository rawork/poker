<!DOCTYPE html>
<html>
  <head>
    <title>Авторизация - {$prj_name}.{$prj_zone}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="{$theme_ref}/bootstrap3/css/bootstrap.min.css" rel="stylesheet">
	<link href="{$theme_ref}/admin/css/login.css" rel="stylesheet">
  </head>
  <body>
	<div class="login-message">
		{if $message}<div class="alert alert-{$message.type}">{$message.text}</div>{/if}
	</div>
	<div class="well login-content">
		<div class="login-header">
		<img src="{$theme_ref}/admin/img/icons/icon_key.gif"> Авторизация
		</div>
		<div class="login-form">
			<form class="form-horizontal" method="post" role="form">
				<div class="form-group">
				  <label for="inputLogin" class="col-sm-2 control-label">Логин</label>
				  <div class="col-sm-10">
					<input type="text" class="form-control" id="inputLogin" name="_user">
				  </div>
				</div>
				<div class="form-group">
				  <label for="inputPassword" class="col-sm-2 control-label">Пароль</label>
				  <div class="col-sm-10">
					<input type="password" class="form-control" id="inputPassword" name="_password">
				  </div>
				</div>
				<div class="form-group">
				  <div class="col-sm-offset-2 col-sm-10">
					<div class="checkbox">
					  <label>
						<input type="checkbox" name="_remember_me"> Запомнить меня
					  </label>
					</div>
				  </div>
				</div>
				<div class="form-group">
				  <div class="col-sm-offset-2 col-sm-10">
					<input type="submit" class="btn btn-default" value="Войти">
				  </div>
				</div>
			</form>
		</div>	
		<div class="login-footer">
			<b>Забыли свой пароль?</b><br>
			Следуйте на <a href="{raURL node=admin method=forget}">форму для запроса пароля</a>.<br>
			<a href="{raURL node=/}" target="_blank">Перейти к просмотру сайта</a>.
		</div>
	</div> 
  </body>
</html>