<?php
require_once 'init.php';
require_once 'get_user.php';

$page_title = 'Дела в порядке';

$search = [
    'text' => '',
    'is_search' => false
];

if (empty($user['id'])) {
    $main_content = include_template('guest.php');
} else {
    // показывать или нет выполненные задачи
    $show_complete_tasks = show_completed();

    // получение списка проектов пользователя
    $projects = get_user_projects($link, $user['id']);

    $tasks = [];

    // Если пользователь включил/выключил чекбокс на задаче
    if (isset($_GET['task_id']) && is_numeric($_GET['task_id'])) {
        $task_id = (integer)$_GET['task_id'];
        toggle_state_task($link, $task_id);

        header("Location: index.php");
    }

    // Если в GET-запросе присутствует параметр search,
    // то в списке задач с помощью полнотекстового поиска ищутся задачи,
    // соответствующие поисковому запросу.
    // Если в GET-запросе имеется параметр project_id,
    // то показываются только те задачи, которые относятся к данному проекту.
    // Если присутствует параметр filter, то показывать только те задачи,
    // которые соответствуют указанному фильтру.
    // Иначе - показывать все задачи
    if (isset($_GET['search'])) {
        $search['text'] = strip_tags(trim($_GET['search']));

        // если поисковый запрос не пустой и не менее трёх символов, то осуществляем поиск
        if (!empty($search['text']) && strlen($search['text']) > 2){
            $search['is_search'] = true;

            $tasks = get_user_tasks_ft_search($link, $user['id'], $search['text']);
        }
    }
    else if (isset($_GET['project_id'])) {
        $project_id = (integer)$_GET['project_id'];

        if (!is_exist_project($link, $user['id'], $project_id)) {
            http_response_code(404);
            exit;
        }

        $tasks = get_user_tasks_project($link, $user['id'], $project_id);
    }
    else if (isset($_GET['filter'])) {
        $filter = $_GET['filter'];

        switch ($filter) {
            case 'today':
                $tasks = get_user_tasks_today($link, $user['id']);
                break;
            case "tomorrow":
                $tasks = get_user_tasks_tomorrow($link, $user['id']);
                break;
            case "expired":
                $tasks = get_user_tasks_expired($link, $user['id']);
                break;
        }
    }
    else {
        $tasks = get_user_tasks_all($link, $user['id']);
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
}

$layout_content = include_template(
    'layout.php',
    [
        'main_content' => $main_content,
        'page_title'=> $page_title,
        'user' => $user
    ]
);

print($layout_content);
