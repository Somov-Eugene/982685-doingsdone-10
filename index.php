<?php
require_once 'auth_user.php';

$page_title = "Дела в порядке";

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

// получение списка проектов пользователя
$projects = get_user_projects($db_link, $user_id);

// Если параметр присутствует, то показывать только те задачи,
// что относятся к этому проекту
if (isset($_GET['project_id']))
{
    $project_id = (integer)$_GET['project_id'];

    // Если значение параметра запроса не существует,
    // то вместо содержимого страницы возвращать код ответа 404
    if (!is_exist_project($db_link, $user_id, $project_id)) {
        http_response_code(404);
        exit(404);
    }

    $tasks = get_user_tasks_project($db_link, $user_id, $project_id);
}
else {
    // Если параметра нет, то показывать все задачи
    $tasks = get_user_tasks_all($db_link, $user_id);
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
        'user_name' => $user_name
    ]
);

print($layout_content);
