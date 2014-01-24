{foreach from=$members item=account}
<div class="member">
	<div class="member-common">
		<img src="{$account.avatar_value.extra.main.path}">
		{$user.group_id_value.item.name}
		{$account.name} {$account.lastname}
	</div>
	<div class="member-info"></div>
	<div class="member-progress"></div>
	<div class="member-vote"></div>
</div>
{/foreach}