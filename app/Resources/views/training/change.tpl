<div class="game-message-container">
	<div class="game-message">
		<div class="row-fluid">
			<div class="span4"><img src="/bundles/public/img/joker.jpg"></div>
			<div class="span8">
				<div class="text">
{if $training->changes == 2}Вы можете поменять одну  карту, ответив на вопрос в течение
13 секунд. Кликните карту, которую хотите заменить, и нажмите "меняю". В 
случае неправильного ответа вы теряете одну фишку.
{else}Вы можете поменять еще одну  карту, ответив на вопрос в течение 13 
секунд. Кликните карту, которую хотите заменить, и нажмите "меняю". В 
случае неправильного ответа вы теряете одну фишку.{/if}</div>
				<button class="btn btn-primary" data-action="change">Меняю</button>
				<button class="btn btn-danger" data-action="nochange">Не меняю</button>
				<div class="timer" id="change-timer"></div>
			</div>
		</div>
	</div>
</div>