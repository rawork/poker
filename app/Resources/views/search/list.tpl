<p>Результаты поиска по запросу &laquo;<b>{$searchText}</b>&raquo;:</p>
{$ptext}<br>
<div class="search-result">
{foreach from=$items item=item}<p>{$item.num}. <a href='{$item.link}'>{$item.title}&nbsp;  &#8594;</a></p><br>
{/foreach}
</div>
{$ptext}