{if !$game->isState(0) && !$game->isState(6)}
	<div>Банк игры: <div id="bank">{$game->getBank()}</div></div>
	<div>Текущие ставки: <div id="bets">{$game->getBets()}</div></div>
{/if}