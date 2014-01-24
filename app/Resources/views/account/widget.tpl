{if $user}
<div class="login-widget">
ДОБРО ПОЖАЛОВАТЬ, <strong>{$user.name}</strong>	
</div>
<ul class="login-menu">
	<li>
		{if $curnode.name == 'members' && $action == 'cabinet'}<span class="text-red">ЛИЧНЫЙ КАБИНЕТ</span>{else}<a href="/members/cabinet">ЛИЧНЫЙ КАБИНЕТ</a>{/if} / 
	</li>
	<li><a href="/members/logout">ВЫЙТИ</a></li>
</ul>	
{else}
<ul class="login-menu">
	<li><a href="/members/cabinet">ВХОД</a> / </li>
	<li><a href="/members/register">РЕГИСТРАЦИЯ</a></li>
</ul>
{/if}