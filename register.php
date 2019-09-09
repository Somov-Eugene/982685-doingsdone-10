<?php
require_once 'init.php';

$page_title = "Дела в порядке - Регистрация аккаунта";

// параметры пользователя со значениями по умолчанию
$user = [
    'email' => '',
    'password' => '',
    'name' => ''
];

$errors = [];

// если форма была отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // сохраняем переданные поля формы
    $user['email'] = get_post_value('email');
    $user['password'] = get_post_value('password');
    $user['name'] = get_post_value('name');

    // правила валидации полей
    $rules = [
        'email' => function ($user) {
            return validate_filled($user, 'email') ?? validate_email($user);
        },
        'password' => function ($user) {
            return validate_filled($user, 'password');
        },
        'name' => function ($user) {
            return validate_filled($user, 'name');
        }
    ];

    foreach ($rules as $field => $validator) {
        if (isset($user[$field]) && is_callable($validator)) {
            $errors[$field] = call_user_func($validator, $user, $field);
        }
    }

    // фильтруем массив, удаляя из него null
    $errors = array_filter($errors);

    // если форма заполнена без ошибок, то проверяем, имеется ли переданный e-mail в базе
    if (count($errors) === 0 && is_exist_user($link, $user['email'])) {
        $errors['email'] = 'Пользователь с таким e-mail уже зарегистрирован';
    }

    if (count($errors) === 0) {
        // получаем hash от переданного пароля
        $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);

        // добавляем нового пользователя в БД
        $user_id = register_user($link, $user);

        if (!empty($user_id)) {
            header("Location: index.php");
        }
    }
}

$main_content = include_template(
    'form-register.php',
    [
        'user' => $user,
        'errors' => $errors
    ]
);

$layout_content = include_template(
    'layout-register.php',
    [
        'main_content' => $main_content,
        'page_title'=> $page_title
    ]
);

print($layout_content);
