Посетитель {$message.author} в  {$message.created} на сайте добавил совет:
<br><br>
{$message.message|nl2br}
<br><br>
Для модерации совета пройдите по ссылке<br>
<a href="http://{$smarty.server.SERVER_NAME}{$prj_ref}/admin/content/catalog/advice/edit/{$message.id}">http://{$smarty.server.SERVER_NAME}{$prj_ref}/admin/content/catalog/advice/edit/{$message.id}</a>