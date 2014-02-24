{foreach from=$boards item=board}
<div class="game-panel">
    <div class="title">{$board.name}</div>
    <div class="title">{$board.fromtime->format('d.m.Y H:i')}</div>
    <div class="title">Состояние: {$board.state}</div>
    <div class="title">Mover: {$board.mover}</div>
    <div class="title{if $board.timer < 0} red{/if}">Timer: {$board.timer}</div>
    <div><button class="btn btn-warning" data-board-id="{$board.id}" data-action="sync">Sync</button></div>
</div>
{/foreach}