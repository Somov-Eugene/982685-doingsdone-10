<?php
date_default_timezone_set('Europe/Moscow');
setlocale(LC_ALL, 'ru-RU');

require_once 'helpers.php';
require_once 'functions.php';
require_once 'database.php';

// Mock-data
$user_email = 'kkk@gmail.com';

// подключение к MySQL
$db_link = db_init('localhost', 'root', '', '982685-doingsdone-10');

if (!$db_link) {
    $errorMsg = 'Ошибка подключения к БД. Дальнейшая работа сайта невозможна!';
    exit($errorMsg);
}

// ОК: cоединение установлено

// получение данных аутентифицированного пользователя
$user = get_user_by_email($db_link, $user_email);

if (empty($user)) {
    $errorMsg = 'Ошибка получения данных пользователя.';
    exit($errorMsg);
}

$user_name = $user['username'];
$user_id = $user['id'];
