<h2>НОВОСТИ</h2>
<div class="row-fluid">
	{foreach from=$items item=news name=news}
	{if $smarty.foreach.news.first || $smarty.foreach.news.index % 4 == 0}<div class="span4">
		<ul class="index-news">{/if}
			<li><span class="date">{$news.date|format_date:'F d, Y'}</span> {$news.name}</li>
		{if ($smarty.foreach.news.index+1) % 4 == 0 || $smarty.foreach.news.last}</ul>
	</div>{/if}
	{/foreach}
</div>