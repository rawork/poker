{if $message}<div class="alert alert-{$message.type}">{$message.text}</div>{/if}
<form action="{raURL node=account method=login}" method="post">
<div class="row-fluid register-card">
	<div class="span6">
		<div class="register-title">Логин <span>(корпоративная почта)</span></div>
		<div class="register-field">
			<input type="text" name="_user">
		</div>
		<div class="register-title">Пароль</div>
		<div class="register-field">
			<input type="password" name="_password">
		</div>
		<div class="register-field">
			<input type="checkbox" name="_remember_me"> Запомнить меня
		</div>
	</div>
</div>
<div class="register-button">
	<button class="btn btn-warning btn-large" type="submit">Войти<small>на сайт</small></button> 
	<a class="user-link" href="/account/forget">забыли пароль?</a>
	<a class="user-link" href="/account/register">регистрация</a>
</div>
</form>