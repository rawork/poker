<div class="row-fluid">
	<div class="span2"><a href="/"><img src="{$theme_ref}/public/img/logo.png"></a></div>
	<div class="span8 game-time" id="game-time"></div>
	<div class="span2 game-exit"><a href="/members/logout">закрыть викторину</a></div>
</div>
<div class="victorina-container">
	{foreach from=$questions item=question name=question}
	{if $smarty.foreach.question.first || $smarty.foreach.question.index % 10 == 0}
		{assign var=num value=$smarty.foreach.question.index/10+1}
		<div class="text-center line line{$num}">{/if}
			<div class="question{if !$question.card} active{/if}" data-question-id="{$question.id}">{if $question.card}<img src="/bundles/public/img/cards/{$question.card}.png">{else}<img src="/bundles/public/img/shirt.png">{/if}</div>
		
	{if ($smarty.foreach.question.index+1) % 10 == 0 || $smarty.foreach.question.last}</div>{/if}
	{/foreach}		
	<div class="victorina-text-container">
		<div class="text" id="victorina-tooltip">
			{if $result.total > 0}
			Вы ответили на {$result.total} из 52 вопросов<br>правильных {$result.correct}
			{else}
			Нажимайте на карты<br>и отвечайте на вопросы!
			{/if}
		</div>
	</div>
</div>
<div class="closed" id="game-question"></div>
<script type="text/javascript">
	
</script>

	
