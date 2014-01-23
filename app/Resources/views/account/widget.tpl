{if $user}
<div class="login-widget">
ДОБРО ПОЖАЛОВАТЬ, <strong>{$user.name}</strong>	
</div>
<ul class="login-menu">
	<li>
		{if $curnode.name == 'account'}<span class="text-red">ЛИЧНЫЙ КАБИНЕТ</span>{else}<a href="/account">ЛИЧНЫЙ КАБИНЕТ</a>{/if} / 
	</li>
	<li><a href="/account/logout">ВЫЙТИ</a></li>
</ul>	
{else}
<ul class="login-menu">
	<li><a href="/account">ВХОД</a> / </li>
	<li><a href="/account/register">РЕГИСТРАЦИЯ</a></li>
</ul>
{/if}