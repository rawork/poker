<div class="club-comment">
	<div class="club-comment-name"><span>{$user.group_id_title}</span> <a class="user-link">{$account.name} {$account.lastname}</a>:</div>
	<div class="text">{$comment.message|nl2br} </div>
	<div class="row-fluid">
		<div class="span4 like"><button class="btn btn-warning" data-action="like-message">ЛАЙК!</button> <img src="/bundles/public/img/heart_club.png"> : <span data-message-id="{$comment.id}">{$comment.likes}</span></div>
		<div class="span3 date">{$comment.created|format_date:"d.m.Y H:i"}</div>
		<div class="span5 slogan">{$account.slogan}</div>
	</div>
</div>