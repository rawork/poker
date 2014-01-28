{if !$isAjax}<div class="member-search-container">
	<div class="member-search">
		<input type="text" name="text" value="{$smarty.session.member_search_text}" placeholder="Поиск участника...">
		<span></span>
	</div>
</div>
<div id="members">{/if}
{foreach from=$members item=account}
<div class="member" data-member-id="{$account.id}">
	<div class="member-common">
		<img class="avatar" src="{if $account.avatar}{$account.avatar_value.extra.main.path}{else}/bundles/public/img/avatar_empty.png{/if}">
		<span class="text-green">{$account.group.title}</span><br>
		<a class="user-link" data-action="card" href="javascript:;">{$account.name} {$account.lastname}</a><br>
		<button class="btn btn-warning" data-action="like">ЛАЙК!</button> <img src="/bundles/public/img/heart.png"> : <span data-like-id="{$account.id}">{$account.likes}</span>
	</div>
	<div class="member-info">
		<ul>
			<li><img src="/bundles/public/img/chip.png"> Фишки: <span class="text-blue">{$account.chips}</span></li>
			<li><img src="/bundles/public/img/goblet.png"> Призы: <span class="text-red">{$account.prizes}</span></li>
			<li><img src="/bundles/public/img/flag.png"> Раунды: <span class="text-green">{$account.rounds}</span></li>
		</ul>
	</div>
	<div class="member-progress">
		<ul>
			{if !$account.is_smarty && !$account.is_leader_quiz && !$account.is_leader_game && !$account.is_soul && !$account.is_leader_like && !$account.is_star}<li class="single">Достижений пока нет :(</li>{/if}
			{if $account.is_smarty}<li><img src="/bundles/public/img/progress1.png" title="Самый умный"> </li>{/if}
			{if $account.is_leader_quiz}<li><img src="/bundles/public/img/progress2.png" title="Лидер в викторине"></li>{/if}
			{if $account.is_leader_game}<li><img src="/bundles/public/img/progress3.png" title="Лидер игры"></li>{/if}
			{if $account.is_soul}<li><img src="/bundles/public/img/progress4.png" title="Душа компании"></li>{/if}
			{if $account.is_leader_like}<li><img src="/bundles/public/img/progress5.png" title="Лидер по лайкам"></li>{/if}
			{if $account.is_star}<li><img src="/bundles/public/img/progress6.png" title="Poker star"></li>{/if}
			{if $account.is_smarty || $account.is_leader_quiz || $account.is_leader_game || $account.is_soul || $account.is_leader_like || $account.is_star}<li>Наведите на бейдж</li>{/if}
		</ul>
		
	</div>
	<div class="member-vote">
		<button data-action="bet" class="btn btn-warning btn-large">Ставлю<small>на победу</small></button>
	</div>
	<div class="clearfix"></div>
</div>
{/foreach}
{$paginator->render()}
{if !$members}<div class="member-search-empty">По заданным параметрам поиска не найдено ни одного участника</div>{/if}
{if !$isAjax}</div>
<div class="member-card"></div>{/if}