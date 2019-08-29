<?php
/**
 * Подсчитывает количество задач для переданного проекта
 *
 * @param $tasks_list array Список задач
 * @param $project_name string Название проекта
 *
 * @return int Количество задач проекта
 */
function number_project_tasks(array $tasks_list, string $project_name) {
    $task_counter = 0;

    foreach ($tasks_list as $task) {
        if ($task['project_name'] === $project_name) {
            $task_counter++;
        }
    }

    return $task_counter;
}


/**
 * Подсчитывает количество часов до выполнения задачи
 *
 * @param $date_completion date Требуемая дата выполнения задачи или null, если задача бессрочная
 *
 * @return int Количество оставшихся часов или null, если задача бессрочная
 */
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


/**
 * Добавляет дополнительные классы для задач, у которых истекает срок выполнения и для выполненных задач
 *
 * @param $tasks_list array Список задач
 * @param $is_show_complete_tasks bool Признак, показывать ли выполненные задачи
 *
 * @return string Название класса
 */
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


/**
 * Формирует ссылку на проект с указанным ID
 *
 * @param $project_id int ID проекта
 *
 * @return string Абсолютный путь к текущей странице с GET-запросом
 */
function get_link_to_project(int $project_id) {
    return '/index.php?project_id=' . $project_id;
}


/**
 * Добавляет дополнительный класс для выделения в меню активного проекта
 *
 * @param $project_id int ID проекта
 *
 * @return string Название класса
 */

function mark_active_project(int $project_id) {
    if (isset($_GET['project_id']) and ((int)$_GET['project_id'] === $project_id)) {
        return 'main-navigation__list-item--active';
    }

    return '';
}
