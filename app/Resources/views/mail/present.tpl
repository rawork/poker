Посетитель {$item.name} в {$item.created} на сайте добавил полезную вещь:
<br><br>
{$item.message|nl2br} <br>
{$item.contact}
<br><br>
Для модерации полезной вещи пройдите по ссылке<br>
<a href="http://{$smarty.server.SERVER_NAME}{$prj_ref}/admin/content/gift/message/edit/{$item.id}">http://{$smarty.server.SERVER_NAME}{$prj_ref}/admin/content/gift/message/edit/{$item.id}</a>