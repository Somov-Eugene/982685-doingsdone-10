<?php
// Mock-data
$user_email = 'kkk@gmail.com';

// получение данных аутентифицированного пользователя
$result = get_user_by_email($link, $user_email);
$user = $result[0];
