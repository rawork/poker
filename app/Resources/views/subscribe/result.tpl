<div class="alert{if $success} alert-success{else} alert-error{/if}">
  <h5>{$message}</h5>
  {if !$success}<a href="{raURL node=subscribe-process}">Перейти к форме подписки</a>{/if}
</div>
<br><br>