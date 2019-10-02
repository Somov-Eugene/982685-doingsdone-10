<?php
require_once 'init.php';
require_once 'get_user.php';

// если пользователь уже был авторизован
if (!empty($user)) {
    header('Location: index.php');
}

$page_title = 'Дела в порядке';

$main_content = include_template('guest.php');

$layout_content = include_template(
    'layout.php',
    [
        'main_content' => $main_content,
        'page_title'=> $page_title,
        'user' => $user
    ]
);

print($layout_content);
