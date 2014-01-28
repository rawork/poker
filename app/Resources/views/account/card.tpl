<a class="close">&times;</a>
<h2>Карточка участника</h2>
<div class="row-fluid user-card" data-member-id="{$account.id}">
	<div class="span4">
		<div class="user-avatar"><img src="{if $account.avatar}{$account.avatar_value.extra.main.path}{else}/bundles/public/img/avatar_empty.png{/if}"></div>
		<div class="member-card-like">
			<button class="btn btn-warning" data-action="like">ЛАЙК!</button> <img src="/bundles/public/img/heart.png"> : <span data-like-id="{$account.id}">{$account.likes}</span>
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
			<li><span>Статус: </span>{$group.title}</li>
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