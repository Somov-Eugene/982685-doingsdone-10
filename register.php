<?php
require_once 'helpers.php';
require_once 'functions.php';
require_once 'database.php';

$page_title = "Дела в порядке - Регистрация аккаунта";

// подключение к MySQL
$db_link = db_init('localhost', 'root', '', '982685-doingsdone-10');

if (!$db_link) {
    $errorMsg = 'Ошибка подключения к БД. Дальнейшая работа сайта невозможна!';
    exit($errorMsg);
}
// ОК: cоединение установлено

// в пустую форму передаем пустые значения
$user = [
    'email' => '',
    'password' => '',
    'name' => ''
];

// ошибки валидации
$errors = [];

// если форма была отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // сохраняем переданные поля формы
    $user['email'] = get_post_value('email');
    $user['password'] = get_post_value('password');
    $user['name'] = get_post_value('name');

    // обязательные к заполнению поля формы
    $required_fields = ['email', 'password', 'name'];

    // правила валидации полей
    $rules = [
        'email' => function () {
            return validate_email('email');
        },
        'password' => function () {
            return validate_filled('password');
        },
        'name' => function () {
            return validate_filled('name');
        }
    ];

    foreach ($user as $key => $value) {
        // для каждого поля проверяем,
        // есть ли для этого поля правило валидации
        if ( isset($rules[$key]) ) {
            // получаем функцию валидации и затем вызываем ее
            $rule = $rules[$key];
            // возможные ошибки сохраняем в массиве $errors
            $errors[$key] = $rule();
        }
    }

    // фильтруем массив, удаляя из него null
    $errors = array_filter($errors);

    // проверка на пустое значение
    foreach($required_fields as $field) {
        if (empty($user[$field])) {
            $errors[$field] = 'Это поле требуется заполнить';
        }
    }

    // если форма заполнена без ошибок, то
    if (count($errors) === 0) {
        // проверяем, имеется ли переданный e-mail в базе
        if (is_exist_user($db_link, $user['email'])) {
            $errors['email'] = 'Пользователь с таким e-mail уже зарегистрирован';
        }
        else {
            // не существует --> добавляем нового пользователя в БД
            $user_id = register_user($db_link, $user);

            if (empty($user_id)) {
                $errorMsg = 'Ошибка при регистрации нового пользователя';
                exit($errorMsg);
            }

            // при успешном добавлении записи переадресовываем на главную страницу
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
