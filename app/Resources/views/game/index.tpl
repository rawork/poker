<div class="row-fluid">
	<div class="span2"><a href="/"><img src="{$theme_ref}/public/img/logo.png"></a></div>
	<div class="span8 game-time" id="game-time">{$game->getName()}</div>
	<div class="span2 game-exit"><a href="/members/logout">выйти из игры</a></div>
</div>
<div class="game-board-container">	
	<div class="game-board">
		<div class="row-fluid">
			<div class="span4 game-min-bet">{$minbet}</div>
			<div class="span4 game-table" id="table">
				{$table}
			</div>
			<div class="span4 game-main-bank">{$bank}</div>
			<div class="clearfix"></div>
		</div>
	</div>
</div>		
<div class="gamer-container">
	{$rivals}
	{$gamerData}
	{$winner}
	{$hint}
	<div class="gamer-out{if $gamer->isState(1) || $gamer->isState(2)} closed{/if}">
		<div class="text">Вы временно вышли из игры. <br> Возвращайтесь и выигрывайте!</div>
	</div>
</div>
<div class="game-combinations"><img src="{$theme_ref}/public/img/combinations4.jpg"></div>
<script type="text/javascript">
	var gameid = {$game->getId()};
	var cardimages = {$deck->names(true)};
</script>

	
