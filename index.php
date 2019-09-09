<?php
require_once 'init.php';
require_once 'get_user.php';    // временно

$page_title = "Дела в порядке";

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

// получение списка проектов пользователя
$projects = get_user_projects($link, $user['id']);

$tasks = [];
// Если параметр присутствует, то показывать только те задачи,
// что относятся к этому проекту
if (isset($_GET['project_id'])) {
    $project_id = (integer)$_GET['project_id'];

    if (!is_exist_project($link, $user['id'], $project_id)) {
        http_response_code(404);
        exit;
    }

    $tasks = get_user_tasks_project($link, $user['id'], $project_id);
} else {
    $tasks = get_user_tasks_all($link, $user['id']);
}

$main_content = include_template(
    'main.php',
    [
        'tasks' => $tasks,
        'projects' => $projects,
        'is_show_complete_tasks' => boolval($show_complete_tasks)
    ]
);

$layout_content = include_template(
    'layout.php',
    [
        'main_content' => $main_content,
        'page_title'=> $page_title,
        'user_name' => $user['name']
    ]
);

print($layout_content);
