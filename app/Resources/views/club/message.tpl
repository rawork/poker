<div class="row-fluid club-chat" data-message-id="{$message.id}">
	<div class="span2">
		<div class="club-avatar"><img src="{if $account.avatar}{$account.avatar_value.extra.main.path}{else}/bundles/public/img/avatar_empty.png{/if}"></div>
		<div class="club-name"><span>{$user.group_id_title}</span><a class="user-link" data-action="card2">{$account.name}<br>{$account.lastname}</a></div>
	</div>
	<div class="span10">
		<div class="club-arrow-container">
			<div class="arrow"></div>
		</div>
		<div class="club-message">
			<div class="text">
				{$message.message|nl2br}
			</div>	
			<div class="row-fluid">
				<div class="span4 like"><button class="btn btn-warning" data-action="like-message">ЛАЙК!</button> <img src="/bundles/public/img/heart_club.png"> : <span data-message-id="{$message.id}">{$message.likes}</span></div>
				<div class="span3 date">{$message.created|format_date:"d.m.Y H:i"}</div>
				<div class="span5 slogan">{$account.slogan}</div>
			</div>	
		</div>
		<div class="club-show-link" data-comments-id="{$message.id}"><a data-action="comments">{if $message.comments_count}Показать комментарии ({$message.comments_count}){else}Комментировать{/if}</a></div>
	</div>
</div>