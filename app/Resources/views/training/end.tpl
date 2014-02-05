<div class="game-message-container">
	<div class="game-message game-end">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">{if $isYou}Вы проиграли все фишки и выбываете из игры. 
В реальной игре вам нужно 
было заранее докупить фишки. Здесь вы можете потренироваться, начав 
заново, в том числе испробовать покупку фишек.{else}Поздравляем, Вы выиграли все фишки :) {/if}</div>
				<button class="btn btn-primary" data-action="start">Начать заново</button>
				<button class="btn btn-danger" data-action="stop">Выйти</button>
				<div class="timer" id="change-timer"></div>
			</div>
		</div>
	</div>
</div>