Участник {$account.name} {$account.lastname} добавил комментарий к сообщению:
<br><br>
{$message|nl2br}
<br><br>
Для модерации комментария пройдите по ссылке<br>
<a href="http://{$smarty.server.SERVER_NAME}{$prj_ref}/admin/content/club/message/edit/{$commentId}">http://{$smarty.server.SERVER_NAME}{$prj_ref}/admin/content/club/message/edit/{$commentId}</a>