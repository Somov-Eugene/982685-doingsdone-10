<?php
require_once 'init.php';

session_start();

$page_title = "Дела в порядке - Вход на сайт";

// параметры пользователя со значениями по умолчанию
$user = [
    'email' => '',
    'password' => ''
];

$errors = [];

// если форма была отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // сохраняем переданные поля формы
    $user['email'] = get_post_value('email');
    $user['password'] = get_post_value('password');

    // правила валидации полей
    $rules = [
        'email' => function ($user) {
            return validate_filled($user, 'email') ?? validate_email($user);
        },
        'password' => function ($user) {
            return validate_filled($user, 'password');
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
    if (count($errors) === 0 and !is_exist_user($link, $user['email'])) {
        // скрываем от злоумышленика тот факт, что пользователя с таким e-mail не существует
        // во избежании получения им адресов существующих пользователей -- выводим одинаковые
        // сообщения об ошибке как в случае ввода неправильного пароля, так и в случае ввода
        // неправильного e-mail
        $errors['password'] = 'Пароль или e-mail введены неправильно';
    }

    if (count($errors) === 0) {
        // находим пользователя с переданным e-mail среди существующих в БД
        $result = get_user_by_email($link, $user['email']);

        if (empty($result)) {
            $errors['password'] = 'Ошибка получения пароля';
        } else {
            $user_reg = $result[0];

            if (!password_verify($user['password'], $user_reg['password'])) {
                $errors['password'] = 'Пароль или e-mail введены неправильно';
            }
        }
    }

    if (count($errors) === 0) {
        // сохраняем данные пользователя в сессиии
        $_SESSION['user'] = $user_reg;

        // и переадресовываем его на главную страницу
        header("Location: index.php");
    }
}

$main_content = include_template(
    'auth.php',
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
