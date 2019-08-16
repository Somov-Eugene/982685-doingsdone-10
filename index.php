<?php
date_default_timezone_set('Europe/Moscow');
setlocale(LC_ALL, 'ru-RU');

require_once 'helpers.php';

$page_title = "Дела в порядке";
$user_name = "Константин";

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

$projects_names = ['Входящие', 'Учеба', 'Работа', 'Домашние дела', 'Авто'];

$tasks = [
    ['name' => 'Собеседование в IT компании', 'date_completion' => '01.12.2018', 'project_name' => 'Работа', 'is_completed' => 0],
    ['name' => 'Выполнить тестовое задание', 'date_completion' => '25.12.2018', 'project_name' => 'Работа', 'is_completed' => 0],
    ['name' => 'Сделать задание первого раздела', 'date_completion' => '21.12.2018', 'project_name' => 'Учеба', 'is_completed' => 1],
    ['name' => 'Встреча с другом', 'date_completion' => '22.12.2018', 'project_name' => 'Входящие', 'is_completed' => 0],
    ['name' => 'Купить корм для кота', 'date_completion' => null, 'project_name' => 'Домашние дела', 'is_completed' => 0],
    ['name' => 'Заказать пиццу', 'date_completion' => null, 'project_name' => 'Домашние дела', 'is_completed' => 0]
];

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
    $hours_left = 100000000;

    if ( !is_null($date_completion) ) {
        $ts_end = strtotime($date_completion);
        $ts_now = strtotime('now');
        $ts_diff = $ts_end - $ts_now;
        $hours_left = floor($ts_diff / 3600);
    }

    return $hours_left;
}

function check_task_important(array $task) {
    $hours_left = hours_left_deadline($task['date_completion']);

    return ($hours_left <= 24) ? "task--important" : "";
}

$main_content = include_template(
    'main.php',
    [
        'tasks' => $tasks,
        'projects_names' => $projects_names,
        'show_complete_tasks' => $show_complete_tasks
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
