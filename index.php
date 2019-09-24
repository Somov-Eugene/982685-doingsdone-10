<?php
require_once 'init.php';
require_once 'get_user.php';

if (empty($user)) {
    header('Location: guest.php');
}

$page_title = 'Дела в порядке';
$project_id = null;
$tasks = [];

$search = [
    'text' => '',
    'is_search' => false
];

// показывать или нет выполненные задачи
$show_complete_tasks = show_completed();

// получение списка проектов пользователя
$projects = get_user_projects($link, $user['id']);

// Если пользователь включил/выключил чекбокс на задаче
if (isset($_GET['task_id']) && is_numeric($_GET['task_id'])) {
    $task_id = (integer)$_GET['task_id'];
    toggle_state_task($link, $task_id);

    header('Location: index.php');
}

if (isset($_GET['project_id'])) {
    $project_id = (integer)$_GET['project_id'];

    if (!is_exist_project($link, $user['id'], $project_id)) {
        http_response_code(404);
        exit;
    }
}

$filter = $_GET['filter'] ?? null;

$tasks = get_user_tasks($link, $user['id'], $project_id, $filter);

if (isset($_GET['search'])) {
    $search['text'] = strip_tags(trim($_GET['search']));

    // если поисковый запрос не пустой и не менее трёх символов, то осуществляем поиск
    if (!empty($search['text']) && strlen($search['text']) > 2) {
        $search['is_search'] = true;

        $tasks = get_user_tasks_ft_search($link, $user['id'], $search['text']);
    }
}

$main_content = include_template(
    'main.php',
    [
        'tasks' => $tasks,
        'projects' => $projects,
        'is_show_complete_tasks' => boolval($show_complete_tasks),
        'search' => $search
    ]
);

$layout_content = include_template(
    'layout.php',
    [
        'main_content' => $main_content,
        'page_title'=> $page_title,
        'user' => $user
    ]
);

print($layout_content);
