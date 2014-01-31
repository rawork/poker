<div class="row-fluid">
	<div class="span2"><a href="/"><img src="{$theme_ref}/public/img/logo.png"></a></div>
	<div class="span8 game-time" id="game-time"></div>
	<div class="span2 game-exit"><a href="/members/logout">выйти из игры</a></div>
</div>
<div class="game-board-container">	
	<div class="game-board">
		<div class="row-fluid">
			<div class="span4 game-min-bet">{$minbet}</div>
			<div class="span4 game-table" id="table">
				{$board}
			</div>
			<div class="span4 game-main-bank">{$bank}</div>
			<div class="clearfix"></div>
		</div>
	</div>
</div>		
<div class="gamer-container">
	{$bots}
	{$gamer}
	{$winner}
</div>
<div class="game-combinations{if $training->board->state == 0}0{/if}"><img src="{$theme_ref}/public/img/combinations4.jpg"></div>
<script type="text/javascript">
	// common game parameters
	var gamestate = {$training->board->state};
	var gamemaxbet = {$training->board->maxbet};
	var gameallin = {$training->board->allin};
	var gamerbet = {$training->gamer->bet};
	var gametimer = '{$training->timer->holder}';
	// training start
	var gametraining = true;
</script>

	
