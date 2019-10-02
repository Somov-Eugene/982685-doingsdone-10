<?php
require_once 'init.php';
require_once 'get_user.php';

if (empty($user)) {
    header('Location: guest.php');
}

$page_title = "Дела в порядке - Добавление задачи";

// получение списка проектов пользователя
$projects = get_user_projects($link, $user['id']);
$project_ids = array_column($projects, 'id');

// параметры задачи со значениями по умолчанию
$task = [
    'name' => '',
    'project' => '',
    'date' => '',
    'file' => '',
    'user_id' => $user['id']
];

$errors = [];

// если форма была отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // сохраняем переданные поля формы
    $task['name'] = get_post_value('name');
    $task['project'] = get_post_value('project');
    $task['date'] = empty($_POST['date']) ? null : get_post_value('date');
    $task['file'] = empty($_POST['file']) ? null : get_post_value('file');

    // правила валидации полей
    $rules = [
        'name' => function ($task) {
            return validate_filled($task, 'name');
        },
        'project' => function ($task) use ($project_ids) {
            return validate_project($project_ids, $task['project']);
        },
        'date' => function ($task) {
            return validate_date($task, 'date');
        }
    ];

    foreach ($rules as $field => $validator) {
        if (isset($task[$field]) && is_callable($validator)) {
            $errors[$field] = call_user_func($validator, $task, $field);
        }
    }

    // фильтруем массив, удаляя из него null
    $errors = array_filter($errors);

    // если к задаче был прикреплен файл, то проверяем загружен ли файл
    if (isset($_FILES['file']['error']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        // загружаем файл, если нет других ошибок валидации
        // если же они есть, то отменяем загрузку файла
        if (count($errors)) {
            $errors['file'] = 'Файл может быть отправлен только после заполнения всех обязательных полей';
        } else {
            // переносим файл в публичную директорию и сохраняем ссылку
            $temp_name = $_FILES['file']['tmp_name'];
            $file_name = uniqid('dd_') . '_'. $_FILES['file']['name'];
            $file_path = __DIR__ . '/uploads/';
            $file_url = '/uploads/' . $file_name;
            $task['file'] = $file_name;

            move_uploaded_file($temp_name, $file_path . $file_name);
        }
    } elseif (isset($_FILES['file']['error']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors['file'] = 'Не удалось загрузить файл';
    }

    // если ошибок валидации нет, то
    if (count($errors) === 0) {
        // добавляем новую запись в БД
        $task_id = add_user_task($link, $task);

        if (!empty($task_id)) {
            header("Location: index.php");
        }
    }
}

// формируем основной контент (форму)
$main_content = include_template(
    'form-task.php',
    [
        'task' => $task,
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
