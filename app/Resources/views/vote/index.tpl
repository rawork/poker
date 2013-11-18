<div id="vote">
<form id="voteForm" method="post">
<input type="hidden" name="key" value="{$key.code}">
{$list}
</form>
{if $key}<a id="voteButton" class="pull-left btn btn-success btn-lg" href="javascript:void(0)" onclick="sendVote()"><img src="{$theme_ref}/public/img/man-small-white.png"> {"I give"|t}</a>{/if}
</div>