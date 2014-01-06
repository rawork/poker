{if $message}<div class="alert alert-{$message.type}">{$message.text}</div>{/if}
<form class="form-horizontal" action="{raURL node=account method=login}" method="post">
  <div class="control-group">
    <label class="control-label" for="inputEmail">Логин</label>
    <div class="controls">
      <input type="text" id="inputEmail" name="_user">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="inputPassword">Пароль</label>
    <div class="controls">
      <input type="password" id="inputPassword" name="_password">
    </div>
  </div>
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        <input type="checkbox" name=_remember_me"> Запомнить меня
      </label>
      <button type="submit" class="btn">Войти</button> &nbsp;&nbsp; <a href="/account/forget">Забыли пароль?</a> &nbsp;&nbsp; <a href="/account/register">Зарегистрироваться</a>
    </div>
  </div>
</form>