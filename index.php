<?php
date_default_timezone_set('Europe/Moscow');
setlocale(LC_ALL, 'ru-RU');

require_once 'helpers.php';
require_once 'functions.php';
require_once 'database.php';

$page_title = "Дела в порядке";
$user_name = "Константин";
$user_email = 'kkk@gmail.com';
$user_id = 0;

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

$projects = [];
$tasks = [];

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

// Если параметр присутствует, то показывать только те задачи,
// что относятся к этому проекту
if (isset($_GET['project_id']))
{
    $project_id = (integer)$_GET['project_id'];

    // Если значение параметра запроса не существует,
    // то вместо содержимого страницы возвращать код ответа 404
    if (!is_exist_project($db_link, $user_id, $project_id)) {
        http_response_code(404);
        die;
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
