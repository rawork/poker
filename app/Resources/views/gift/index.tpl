{foreach from=$items item=item}
<div class="catalog-item">
	<div class="time pull-right">{$item.created|format_date:"d.m.Y H:i"}</div>
	<div class="title">{*{$item.id}.*} {$item.name}</div>
	<div>{$item.message|nl2br}</div>
	<a data-lightbox="roadtrip" href="{$item.foto_value.extra.medium.path}"><img src="{$item.foto_value.extra.small.path}"></a>
</div>
{if $item.answer}
<div class="feedback">
	<div class="title">{"Organizing committee"|t}</div>
	<img src="{$theme_ref}/public/img/plushka_{$locale}.png">
	<div>{$item.answer}</div>
</div>
<br><br>
{/if}
{/foreach}
<div>{$paginator->render()}</div>