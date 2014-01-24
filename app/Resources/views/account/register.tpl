{if $message}<div class="alert alert-{$message.type}">{$message.text}</div>{/if}
<form enctype="multipart/form-data" method="post">
<div class="row-fluid register-card">
	<div class="span6">
		<div class="register-title">Выберите статус *</div>
		<div class="register-field">
			<label>
				<select name="group_id">
					<option value="2"{if $register.group_id == 2} selected="true"{/if}>Игрок</option>
					<option value="3"{if $register.group_id == 3} selected="true"{/if}>Наблюдатель</option>
				</select>
			</label>	
		</div>
		<div class="register-title">Имя *</div>
		<div class="register-field">
			<input type="text" name="name" value="{$register.name}">
		</div>
		<div class="register-title">Фамилия *</div>
		<div class="register-field">
			<input type="text" name="lastname" value="{$register.lastname}">
		</div>
		<div class="register-title">Логин <span>(корпоративная почта)</span> *</div>
		<div class="register-field">
			<input type="text" name="login" value="{$register.login}">
		</div>
		<div class="register-title">Пароль *</div>
		<div class="register-field">
			<input type="password" name="password">
		</div>
		<div class="register-title">Подтвердить пароль *</div>
		<div class="register-field">
			<input type="password" name="password_again">
		</div>
	</div>
	<div class="span6">
		<div class="register-title">СБЕ</div>
		<div class="register-field">
			<input type="text" name="sbe" value="{$account.sbe}">
		</div>
		<div class="register-title">Город</div>
		<div class="register-field">
			<input type="text" name="city" value="{$account.city}">
		</div>
		<div class="register-title">Должность <span>(реальная или шуточная)</span></div>
		<div class="register-field">
			<input type="text" name="position" value="{$account.position}">
		</div>
		<div class="register-title">Девиз</div>
		<div class="register-field">
			<input type="text" name="slogan" value="{$account.slogan}">
		</div>
		<div class="register-title">Загрузка аватара</div>
		<div class="register-avatar">
			<a class="remove-icon text-right" href="javascript:;">&times;</a>
			<span class="file-label">Загрузите свою фотографию</span>
			<input type="file" name="avatar">
		</div>
	</div>
</div>
<div class="register-button text-center">
	<button class="btn btn-warning btn-large" type="submit">Отправить<small>данные</small></button> 
	<a class="user-link" href="/members/cabinet">Войти на сайт</a>
</div>
</form>