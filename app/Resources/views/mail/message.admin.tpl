Участник {$account.name} {$account.lastname} добавил сообщение:
<br><br>
{$message|nl2br}
<br><br>
Для модерации сообщения пройдите по ссылке<br>
<a href="http://{$smarty.server.SERVER_NAME}/admin/content/club/message/edit/{$messageId}">http://{$smarty.server.SERVER_NAME}/admin/content/club/message/edit/{$messageId}</a>