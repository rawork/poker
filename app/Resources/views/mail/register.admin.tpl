Новый участник {$account.name} {$account.lastname}:
<br><br>
Логин: {$user.login}<br>
СБЕ: {$account.sbe_id_value.item.name}<br>
Город: {$account.city}<br>
Должность: {$account.position}<br>
Девиз: {$account.slogan}<br>
<br><br>
Для модерации участника пройдите по ссылке<br>
<a href="http://{$smarty.server.SERVER_NAME}{$prj_ref}/admin/content/account/member/edit/{$memberId}">http://{$smarty.server.SERVER_NAME}{$prj_ref}/admin/content/account/member/edit/{$memberId}</a>