<div class="club-comments" id="comments{$message.id}">
	{foreach from=$comments item=comment}
	<div class="club-comment">
		<div class="club-comment-name"><span>{$comment.account.user_id_value.item.title}</span> <a class="user-link" href="javascript:;">{$comment.account.name} {$comment.account.lastname}</a>:</div>
		<div class="text">{$comment.message|nl2br}</div>
		<div class="row-fluid">
			<div class="span4 like"><button class="btn btn-warning" data-action="like-message">ЛАЙК!</button> <img src="/bundles/public/img/heart_club.png"> : <span data-message-id="{$comment.id}">{$comment.likes}</span></div>
			<div class="span3 date">{$comment.created|format_date:"d.m.Y H:i"}</div>
			<div class="span5 slogan">{$comment.account.slogan}</div>
		</div>
	</div>
	{/foreach}
</div>
{if $user}<div class="club-add-comment">
	Добавить комментарий:<br>
	<div contenteditable="true" class="text"></div>
	<div class="row-fluid">
		<div class="span6"><a data-action="show-smiles">Добавить смайл</a></div>
		<div class="span6 text-right"><button class="btn btn-primary" data-action="comment">ОТПРАВИТЬ</button></div>	 
	</div>
</div>{/if}
<div class="club-hide-link" id="hide-message{$message.id}">
	<a data-action="hide">скрыть комментарии</a>
</div>
