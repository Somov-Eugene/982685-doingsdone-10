<?php
date_default_timezone_set('Europe/Moscow');
// set_locale(LC_ALL, 'ru-RU');
// Fatal error: Uncaught Error: Call to undefined function set_locale() in D:\OSPanel\domains\doingsdone\index.php:3
// Stack trace: #0 {main} thrown in D:\OSPanel\domains\doingsdone\index.php on line 3

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
    ['name' => 'Купить корм для кота', 'date_completion' => 'Нет', 'project_name' => 'Домашние дела', 'is_completed' => 0],
    ['name' => 'Заказать пиццу', 'date_completion' => 'Нет', 'project_name' => 'Домашние дела', 'is_completed' => 0]
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

function hours_left_deadline(string $date_completion) {
    if ($date_completion === 'Нет') {
        $hours_left = 100000000;
    }
    else {
        // $dt_end = date_create($date_completion);
        // $dt_now = date_create('now');
        // $dt_diff = date_diff($dt_end, $dt_now);
        // $hours_left = date_interval_format($dt_diff, '%h');

        $ts_end = strtotime($date_completion);
        $ts_now = strtotime('now');
        $ts_diff = $ts_end - $ts_now;
        $hours_left = floor($ts_diff / 3600);
    }

    return $hours_left;
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
