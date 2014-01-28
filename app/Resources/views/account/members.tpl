<hr class="red-line">
<div class="row-fluid">
	<div class="span12 text-center">
		<h3>УЧАСТНИКИ КЛУБА:</h3>
		<ul class="index-members">
			{foreach from=$members item=account}
			<li><img title="{$account.name} {$account.lastname}" src="{if $account.avatar}{$account.avatar_value.extra.main.path}{else}/bundles/public/img/avatar_empty.png{/if}"></li>
			{/foreach}
		</ul>
		<a class="btn btn-success btn-large" href="/members">УЧАСТНИКИ<small>клуба</small></a>
		<br><br><br>
	</div>
</div>