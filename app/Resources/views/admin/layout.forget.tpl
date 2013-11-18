<!DOCTYPE html>
<html>
	<head>
		<title>Восстановление пароля - {$prj_name}.{$prj_zone}</title>
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
				<img src="{$theme_ref}/admin/img/icons/icon_key.gif"> Восстановление пароля
			</div>
			<div class="login-form">
				<form class="form-horizontal" method="post">
					<div class="form-group">
					  <label for="inputLogin" class="col-sm-4 control-label">Логин<br> или эл. почта</label>
					  <div class="col-sm-8">
					    <input type="text" class="form-control" id="inputLogin" name="_user">
					  </div>
					</div>
					<div class="form-group">
					  <div class="col-sm-offset-4 col-sm-8">
						<input type="submit" class="btn btn-default" value="Отправить">
					  </div>
					</div>
				</form>
			<div class="login-footer">
				Если вы забыли пароль, введите ваш Логин или адрес Электронной почты, указанный при регистрации.<br> 
				Новый пароль будет выслан вам по электронной почте.<br>
				Вернуться на <a href="{raURL node=admin}/">форму авторизации</a>.
			</div>
		</div> 
	</body>
</html>