<?php
if (!file_exists("config/db.php")) {
    echo 'Добавьте файл конфигурации подключения к БД: config/db.php';
    exit;
}

require_once 'config/db.php';

$link = mysqli_connect($database_host, $database_user, $database_password, $database_name);

if (!$link) {
    $errorMsg = 'Ошибка подключения к БД. Дальнейшая работа сайта невозможна!';
    exit($errorMsg);
}

mysqli_set_charset($link, 'utf8');

mysqli_options($link, MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
