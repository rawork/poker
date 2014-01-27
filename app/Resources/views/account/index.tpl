{foreach from=$members item=account}
<div class="member">
	<div class="member-common">
		<img class="avatar" src="{if $account.avatar}{$account.avatar_value.extra.main.path}{else}/bundles/public/img/avatar_empty.png{/if}">
		<span class="text-green">{$account.group.title}</span><br>
		<a class="user-link" href="javascript:;">{$account.name} {$account.lastname}</a><br>
		<input class="btn btn-warning" data-member-id="{$account.id}" type="button" value="ЛАЙК!"> <img src="/bundles/public/img/heart.png"> : <span id="like-counter-{$account.id}">{$account.likes}</span>
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
		<a class="btn btn-warning btn-large">Ставлю<small>на победу</small></a>
	</div>
	<div class="clearfix"></div>
</div>
{/foreach}
{$paginator->render()}
<div class="member-card" style="display:none;">
	<a class="close">&times;</a>
	<h2>Карточка участника</h2>
	<div class="row-fluid user-card">
	<div class="span4">
		<div class="user-avatar"><img src="{if $account.avatar}{$account.avatar_value.extra.main.path}{else}/bundles/public/img/avatar_empty.png{/if}"></div>
		<div class="member-card-like">
			<input class="btn btn-warning" type="button" value="ЛАЙК!"> <img src="/bundles/public/img/heart.png"> : <span id="like-counter-{$account.id}">{$account.likes}</span>
		</div>
		<ul class="member-card-info">
			<li><img src="/bundles/public/img/chip.png"> Фишки: <span class="text-blue">{$account.chips}</span></li>
			<li><img src="/bundles/public/img/goblet.png"> Призы: <span class="text-red">{$account.prizes}</span></li>
			<li><img src="/bundles/public/img/flag.png"> Раунды: <span class="text-green">{$account.rounds}</span></li>
		</ul>
	</div>
	<div class="span8 user-center">
		<div class="member-card-name">
			{$account.name}<br>{$account.lastname}
		</div>
		<ul class="member-card-data">
			<li><span>Статус: </span>{$account.group.title}</li>
			<li><span>СБЕ: </span>{$account.sbe}</li>
			<li><span>Должность: </span>{$account.position}</li>
			<li><span>Девиз: </span>{$account.slogan}</li>
		</ul>
		
		<ul class="member-card-progress">
			{if !$account.is_smarty && !$account.is_leader_quiz && !$account.is_leader_game && !$account.is_soul && !$account.is_leader_like && !$account.is_star}<li>Достижений пока нет :(<li>{/if}
			{if $account.is_smarty}<li><img src="/bundles/public/img/progress1.png"> Самый умный</li>{/if}
			{if $account.is_leader_quiz}<li><img src="/bundles/public/img/progress2.png"> Лидер в викторине</li>{/if}
			{if $account.is_leader_game}<li><img src="/bundles/public/img/progress3.png"> Лидер игры</li>{/if}
			{if $account.is_soul}<li><img src="/bundles/public/img/progress4.png"> Душа компании</li>{/if}
			{if $account.is_leader_like}<li><img src="/bundles/public/img/progress5.png"> Лидер по лайкам</li>{/if}
			{if $account.is_star}<li><img src="/bundles/public/img/progress6.png"> Poker star</li>{/if}
		</ul>
		<div class="clearfix"></div>
	</div>
</div>
</div>