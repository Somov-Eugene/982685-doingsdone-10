<?php
date_default_timezone_set('Europe/Moscow');
setlocale(LC_ALL, 'ru-RU');

require_once 'helpers.php';

$page_title = "Дела в порядке";
$user_name = "Константин";

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

// $projects_names = ['Входящие', 'Учеба', 'Работа', 'Домашние дела', 'Авто'];
$projects_names = [];

// $tasks = [
//     ['name' => 'Собеседование в IT компании', 'date_completion' => '2018-12-01', 'project_name' => 'Работа', 'is_completed' => 0],
//     ['name' => 'Выполнить тестовое задание', 'date_completion' => '2018-12-25', 'project_name' => 'Работа', 'is_completed' => 0],
//     ['name' => 'Сделать задание первого раздела', 'date_completion' => '2018-12-21', 'project_name' => 'Учеба', 'is_completed' => 1],
//     ['name' => 'Встреча с другом', 'date_completion' => '2018-12-22', 'project_name' => 'Входящие', 'is_completed' => 0],
//     ['name' => 'Купить корм для кота', 'date_completion' => null, 'project_name' => 'Домашние дела', 'is_completed' => 0],
//     ['name' => 'Заказать пиццу', 'date_completion' => null, 'project_name' => 'Домашние дела', 'is_completed' => 0]
// ];
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

// подключение к MySQL
$db_link = mysqli_connect('localhost', 'root', '', '982685-doingsdone-10');
$error = '';

if (!$db_link) {
    // oшибка подключения к БД
    $error = mysqli_connect_error();
}
else {
    // ОК: cоединение установлено

    // установка кодировки
    mysqli_set_charset($db_link, 'utf8');

    // включить преобразование типов для INT и FLOAT
    mysqli_options($db_link, MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);

    // получение ID текущего пользователя
    $sql = "SELECT u.`id` FROM users u WHERE u.`username` = ?";
    $stmt = db_get_prepare_stmt($db_link, $sql, [$user_name]);
    mysqli_stmt_execute($stmt);
    $sql_result = mysqli_stmt_get_result($stmt);

    if (!$sql_result) {
        $error = mysqli_error($db_link);
    }
    else {
        $rows = mysqli_fetch_all($sql_result, MYSQLI_ASSOC);
        foreach ($rows as $row) {
            $user_id = $row['id'];
        }

        // получение списка проектов текущего пользователя
        $sql = "SELECT p.`name` FROM projects p WHERE p.`user_id` = '" . $user_id . "'";
        $sql_result = mysqli_query($db_link, $sql);

        if (!$sql_result) {
            $error = mysqli_error($db_link);
        }
        else {
            $rows = mysqli_fetch_all($sql_result, MYSQLI_ASSOC);
            foreach ($rows as $row) {
                $projects_names[] = $row['name'];
            }
        }

        // получение списка задач текущего пользователя
        $sql = "SELECT t.`is_completed`, t.`name`, t.`dt_completion` AS date_completion, p.`name` AS project_name"
             ." FROM tasks t JOIN projects p ON p.`id` = t.`project_id`"
             ." WHERE t.`user_id` = '" . $user_id . "'";
        $sql_result = mysqli_query($db_link, $sql);

        if (!$sql_result) {
            $error = mysqli_error($db_link);
        }
        else {
            $tasks = mysqli_fetch_all($sql_result, MYSQLI_ASSOC);
        }
    }
}

$main_content = include_template(
    'main.php',
    [
        'tasks' => $tasks,
        'projects_names' => $projects_names,
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
