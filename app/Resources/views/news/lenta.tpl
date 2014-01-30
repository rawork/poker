<h2>НОВОСТИ</h2>
<div class="row-fluid">
	{foreach from=$items item=news name=news}
	{if $smarty.foreach.news.first || $smarty.foreach.news.index % $num == 0}<div class="row-fluid">{/if}
		<div class="span6 index-news">
			<div class="title">{$news.name}</div>
			<div><span class="date">{$news.created|format_date:'F d, Y'}</span> {$news.preview}</div>
		</div>	
	{if ($smarty.foreach.news.index+1) % $num == 0 || $smarty.foreach.news.last}</div>{/if}
	{/foreach}
</div>
<div class="text-center"><a class="btn btn-success btn-large" href="/club">ОБСУДИТЬ<small>в клубе</small></a></div>