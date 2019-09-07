<?php
// получение данных аутентифицированного пользователя
$user = get_user_by_email($db_link, $user_email);

if (empty($user)) {
    $errorMsg = 'Ошибка получения данных пользователя.';
    exit($errorMsg);
}

$user_name = $user['username'];
$user_id = (integer)$user['id'];
