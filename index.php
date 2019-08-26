<?php
date_default_timezone_set('Europe/Moscow');
setlocale(LC_ALL, 'ru-RU');

require_once 'helpers.php';
require_once 'database.php';

$page_title = "Дела в порядке";
$user_name = "Константин";
$user_email = 'kkk@gmail.com';
$user_id = 0;

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

$projects = [];
$tasks = [];

function number_project_tasks(array $tasks_list, string $project_name) {
    $task_counter = 0;

    foreach ($tasks_list as $task) {
        if ($task['project_name'] === $project_name) {
            $task_counter++;
        }
    }

    return $task_counter;
}

function hours_left_deadline($date_completion) {
    if (is_null($date_completion)) {
        return null;
    }

    $ts_end = strtotime($date_completion);
    $ts_now = strtotime('now');
    $ts_diff = $ts_end - $ts_now;
    $hours_left = floor($ts_diff / 3600);

    return $hours_left;
}

function additional_task_classes(array $task, bool $is_show_complete_tasks) {
    if (boolval($task['is_completed']) and $is_show_complete_tasks) {
        return 'task--completed';
    }

    $hours_left = hours_left_deadline($task['date_completion']);
    if (!is_null($hours_left) and $hours_left <= 24) {
        return 'task--important';
    }

    return '';
}

function get_link_to_project(int $project_id) {
    return "/" . pathinfo(__FILE__, PATHINFO_BASENAME) . '?id=' . (string)$project_id;
}

// подключение к MySQL
$db_link = db_init('localhost', 'root', '', '982685-doingsdone-10');

if (!$db_link) {
    // oшибка подключения к БД
    $errorMsg = 'Ошибка подключения к БД. Дальнейшая работа сайта невозможна!';
    die($errorMsg);
}

// ОК: cоединение установлено

// получение ID текущего пользователя
$user = get_user_by_email($db_link, $user_email);
if ($user) {
    $user_id = $user["id"];
}

// получение списка проектов текущего пользователя
$projects = get_user_projects($db_link, $user_id);

// получение списка задач текущего пользователя
$tasks = get_user_tasks($db_link, $user_id);

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
