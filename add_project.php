<?php
require_once 'init.php';
require_once 'get_user.php';

if ($is_anonymous) {
    header("Location: index.php");
}

$page_title = "Дела в порядке - Добавление проекта";

// получение списка проектов пользователя
$projects = get_user_projects($link, $user['id']);
$projects_names = array_column($projects, 'name');

// параметры проекта со значениями по умолчанию
$new_project = [
    'name' => '',
    'user_id' => $user['id']
];

$errors = [];

// если форма была отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // сохраняем переданные поля формы
    $new_project['name'] = get_post_value('name');

    // правила валидации полей
    $rules = [
        'name' => function ($new_project) {
            return validate_filled($new_project, 'name');
        }
    ];

    foreach ($rules as $field => $validator) {
        if (isset($new_project[$field]) && is_callable($validator)) {
            $errors[$field] = call_user_func($validator, $new_project, $field);
        }
    }

    // фильтруем массив, удаляя из него null
    $errors = array_filter($errors);

    // если ошибок валидации нет, то проверяем уникальность названия проекта
    if (count($errors) === 0 && in_array($new_project['name'], $projects_names)) {
        $errors['name'] = "Проект с таким названием уже существует";
    }

    if (count($errors) === 0) {
        // добавляем новую запись в БД
        $project_id = add_user_project($link, $new_project);

        if (!empty($project_id)) {
            header("Location: index.php");
        }
    }
}

// формируем основной контент (форму)
$main_content = include_template(
    'form-project.php',
    [
        'new_project' => $new_project,
        'errors' => $errors,
        'projects' => $projects
    ]
);

// добавляем основной контент в layout
$layout_content = include_template(
    'layout.php',
    [
	    'main_content' => $main_content,
        'page_title'=> $page_title,
        'user' => $user
    ]
);

print($layout_content);
