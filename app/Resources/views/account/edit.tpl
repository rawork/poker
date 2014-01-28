{if $message}<div class="alert alert-{$message.type}">{$message.text}</div>{/if}
<form enctype="multipart/form-data" method="post">
<input type="hidden" name="id" value="{$account.id}">
<input type="hidden" name="user_id" value="{$account.user_id}">
<div class="row-fluid register-card">
	<div class="span6">
		<div class="register-title">Выберите статус *</div>
		<div class="register-field">
			<label>
				<select name="group_id">
					{if $account.user_id_value.item.is_admin == 1}<option value="1"{if $account.user_id_value.item.group_id == 1} selected="true"{/if}>Администратор</option>{/if}
					<option value="2"{if $account.user_id_value.item.group_id == 2} selected="true"{/if}>Игрок</option>
					<option value="3"{if $account.user_id_value.item.group_id == 3} selected="true"{/if}>Зритель</option>
				</select>
			</label>	
		</div>
		<div class="register-title">Имя *</div>
		<div class="register-field">
			<input type="text" name="name" value="{$account.name}">
		</div>
		<div class="register-title">Фамилия *</div>
		<div class="register-field">
			<input type="text" name="lastname" value="{$account.lastname}">
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
		<div class="register-title">Обновление аватара <span>(оставьте пустым для сохранения прежнего аватара)</span></div>
		<div class="register-avatar">
			<a class="remove-icon text-right" href="javascript:;">&times;</a>
			<span class="file-label">Загрузите новую фотографию</span>
			<input type="file" name="avatar">
		</div>
	</div>
</div>
<div class="register-button text-center">
	<button class="btn btn-warning btn-large" type="submit">Сохранить<small>изменения</small></button> 
	<a class="user-link" href="/members/cabinet">отменить</a>
</div>
</form>