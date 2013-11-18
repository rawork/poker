{foreach from=$messages item=message}
<div class="catalog-item">
	<div class="time pull-right">{$message.created|format_date:"d.m.Y H:i"}</div>
	<div class="title">{$message.author}</div>
	<div>{$message.message}</div>
</div>
<div id="comments{$message.id}">
{foreach from=$message.children item=comment}
<div class="feedback">
	<div class="time pull-right">{$comment.created|format_date:"d.m.Y H:i"}</div>
	<div class="title">{$comment.author}</div>
	<div>{$comment.message}</div>
</div>
{/foreach}
</div>
<div class="comment">
	<a class="btn btn-link btn-sm" onclick="$('#commentForm{$message.id}').toggleClass('hidden')">{"Add comment"|t}</a>
	<div id="commentResult{$message.id}" class="label hidden"></div>
	<form id="commentForm{$message.id}" role="form" class="hidden">
		<div class="form-group">
		  <label for="inputAuthor{$message.id}">{"Name"|t}</label>
		  <input type="text" value="{$smarty.cookies.authorname}" name="author" class="form-control input-sm" id="inputAuthor{$message.id}">
		</div>
		<div class="form-group">
		  <label for="inputMessage{$message.id}">{"Comment"|t}</label>
		  <textarea name="message" class="form-control" id="inputMessage{$message.id}"></textarea>
		</div>
		<input type="button" onclick="sendAdviceComment({$message.present_id}, {$message.id})" class="btn btn-default btn-sm" value="{"Send"|t}">
		<br><br>
	</form>
</div>
{/foreach}