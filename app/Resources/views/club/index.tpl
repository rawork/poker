{if !$isAjax}
{if $user}
<div class="row-fluid club-chat">
	<div class="span2">
		<div class="club-avatar"><img src="{if $account.avatar}{$account.avatar_value.extra.main.path}{else}/bundles/public/img/avatar_empty.png{/if}"></div>
		<div class="club-name"><span>{$user.group_id_title}</span><a class="user-link" data-action="card2">{$user.name}<br>{$user.lastname}</a></div>
	</div>
	<div class="span10">
		<div class="club-arrow2-container">
			<div class="arrow2"></div>
		</div>
		<div class="club-add-message">
			<div contenteditable="true" class="text"></div>
			<div class="row-fluid">
				<div class="span6"><a data-action="show-smiles">Добавить смайл</a></div>
				<div class="span6 text-right"><button class="btn btn-primary" data-action="message">ОТПРАВИТЬ</button></div>	 
			</div>
		</div>
	</div>
</div>
{else}
	<div class="club-nouser">Для добавления сообщений и комментариев необходимо <a href="/members/cabinet">войти на сайт</a> или <a href="/members/register">зарегистрироваться</a></div>
{/if}
{/if}
<div id="messages">
	{foreach from=$messages item=message}
	<div class="row-fluid club-chat" data-message-id="{$message.id}">
		<div class="span2">
			<div class="club-avatar"><img src="{if $message.account.avatar}{$message.account.avatar_value.extra.main.path}{else}/bundles/public/img/avatar_empty.png{/if}"></div>
			<div class="club-name"><span>{$message.user.group_id_value.item.title}</span><a class="user-link" data-action="card2">{$message.account.name}<br>{$message.account.lastname}</a></div>
		</div>
		<div class="span10" id="message{$message.id}">
			<div class="club-arrow-container">
				<div class="arrow"></div>
			</div>
			<div class="club-message">
				<div class="text">
					{$message.message}
				</div>	
				<div class="row-fluid">
					<div class="span4 like"><button class="btn btn-warning" data-action="like-message">ЛАЙК!</button> <img src="/bundles/public/img/heart_club.png"> : <span data-message-id="{$message.id}">{$message.likes}</span></div>
					<div class="span3 date">{$message.created|format_date:"d.m.Y H:i"}</div>
					<div class="span5 slogan">{$message.account.slogan}</div>
				</div>	
			</div>
			<div class="club-show-link"><a data-action="comments">{if $message.comments_count}Показать комментарии ({$message.comments_count}){else}{if $user}Комментировать{/if}{/if}</a></div>	
		</div>
	</div>
	{/foreach}	
{if !$isAjax}</div>
<div class="member-card"></div>
<div class="smiles-container">
	<div class="smiles">
		<div class="arrow"></div>
		<div class="list">
			{section name=smile start=1 loop=33 step=1}
			<i class="smile smile{$smarty.section.smile.index}"></i>	
			{/section}
		</div>
		
	</div>
</div>{/if}