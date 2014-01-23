{if $message}<div class="alert alert-{$message.type}">{$message.text}</div>{/if}
<p>В форму ниже введите свой электронный адрес, указанный при регистрации, и через несколько минут на Ваш E-mail придет письмо с паролем</p>
<form action="{raURL node=account method=forget}" method="post">
<div class="row-fluid register-card">
	<div class="span6">
		<div class="register-title">Логин <span>(корпоративная почта)</span></div>
		<div class="register-field">
			<input type="text" name="email">
		</div>
	</div>
</div>
<div class="register-button">
	<button class="btn btn-warning btn-large" type="submit">Выслать<small>пароль</small></button>
	<a class="user-link" href="/account">Войти на сайт</a>
</div>
</form>