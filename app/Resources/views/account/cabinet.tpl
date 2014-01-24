<div class="row-fluid user-card">
	<div class="span3">
		<div class="user-avatar"><img src="{$account.avatar_value.extra.main.path}"></div>
		<ul class="user-info">
			<li><img src="/bundles/public/img/chip.png"> Фишки: <span class="text-blue">{$account.chips}</span></li>
			<li><img src="/bundles/public/img/goblet.png"> Призы: <span class="text-red">{$account.prizes}</span></li>
			<li><img src="/bundles/public/img/flag.png"> Раунды: <span class="text-green">{$account.rounds}</span></li>
		</ul>
	</div>
	<div class="span6 user-center">
		<div class="user-name">
			{$user.name}<br>{$user.lastname}
		</div>
		<div class="user-like">
			<input class="btn btn-warning" type="button" value="ЛАЙК!"> <img src="/bundles/public/img/heart.png"> : <span id="like-counter-{$account.id}">{$account.likes}</span>
		</div>	
		<ul class="user-data">
			<li><span>Статус: </span>{$group.title}</li>
			<li><span>Логин: </span>{$user.login}</li>
			<li><span>СБЕ: </span>{$account.sbe}</li>
			<li><span>Должность: </span>{$account.position}</li>
			<li><span>Девиз: </span>{$account.slogan}</li>
		</ul>
		<a class="user-link" href="/members/edit">редактировать анкету</a>
	</div>
	<div class="span3 text-right">
		<ul class="user-progress text-left">
			{if !$account.is_smarty && !$account.is_leader_quiz && !$account.is_leader_game && !$account.is_soul && !$account.is_leader_like && !$account.is_star}Достижений пока нет :({/if}
			{if $account.is_smarty}<li><img src="/bundles/public/img/progress1.png"> Самый умный</li>{/if}
			{if $account.is_leader_quiz}<li><img src="/bundles/public/img/progress2.png"> Лидер в викторине</li>{/if}
			{if $account.is_leader_game}<li><img src="/bundles/public/img/progress3.png"> Лидер игры</li>{/if}
			{if $account.is_soul}<li><img src="/bundles/public/img/progress4.png"> Душа компании</li>{/if}
			{if $account.is_leader_like}<li><img src="/bundles/public/img/progress5.png"> Лидер по лайкам</li>{/if}
			{if $account.is_star}<li><img src="/bundles/public/img/progress6.png"> Poker star</li>{/if}
		</ul>
	</div>
</div>