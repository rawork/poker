{foreach from=$items item=item}
<div class="row">
	<div class="col-md-3">
		<a data-lightbox="roadtrip{$item.id}" href="{$item.foto_value.extra.medium.path}"><img src="{$item.foto_value.extra.small.path}"></a>
		<div class="wishlist"><a href="javascript:void(0)" onclick="showListDialog({$item.id}, '{$curnode.name}')" class="btn btn-default"><img class="pull-left" src="{$theme_ref}/public/img/stars-small.png"> {"The list<br>of needed<br>goods"|t}</a></div>	
	</div>
	<div class="col-md-9">
		<div>
			{foreach from=$item.gallery_value item=file}<a data-lightbox="roadtrip{$item.id}" href="{$file.extra.medium.path}"><img src="{$file.extra.small.path}"></a> {/foreach}</div>
		<div class="partner">{$item.name}</div>
		<div class="site">{$item.address}</div>
		{$item.description}
	</div>
</div>
<br><br>
{/foreach}