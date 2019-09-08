<?php
$link = mysqli_connect('localhost', 'root', '', '982685-doingsdone-10');

if (!$link) {
    $errorMsg = 'Ошибка подключения к БД. Дальнейшая работа сайта невозможна!';
    exit($errorMsg);
}

mysqli_set_charset($link, 'utf8');

mysqli_options($link, MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
