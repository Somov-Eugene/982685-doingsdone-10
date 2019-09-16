<?php
/**
 * @deprecated Подсчитывает количество задач для переданного проекта
 *
 * @param array $tasks_list Список задач
 * @param string $project_name Название проекта
 *
 * @return int Количество задач проекта
 */
function number_project_tasks(array $tasks_list, string $project_name)
{
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
 * @param date $date_completion Требуемая дата выполнения задачи или null, если задача бессрочная
 *
 * @return int Количество оставшихся часов или null, если задача бессрочная
 */
function hours_left_deadline($date_completion)
{
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
 * @param array $tasks_list Список задач
 * @param bool $is_show_complete_tasks Признак, показывать ли выполненные задачи
 *
 * @return string Название класса
 */
function additional_task_classes(array $task, bool $is_show_complete_tasks)
{
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
 * @param int $project_id ID проекта
 *
 * @return string Абсолютный путь к текущей странице с GET-запросом
 */
function get_link_to_project(int $project_id)
{
    return '/index.php?project_id=' . $project_id;
}


/**
 * Добавляет дополнительный класс для выделения в меню активного проекта
 *
 * @param int $project_id ID проекта
 *
 * @return string Название класса
 */
function mark_active_project(int $project_id)
{
    if (isset($_GET['project_id']) and ((int)$_GET['project_id'] === $project_id)) {
        return 'main-navigation__list-item--active';
    }

    return '';
}


/**
 * Определяет, показывать или нет выполненные задачи в зависимости от переданного GET-запроса
 *
 * @return int Значение 1 - показывать, 0 - не показывать
 */
function show_completed()
{
    $result = 0;

    if (isset($_GET['show_completed']) && is_numeric($_GET['show_completed'])) {
        $result = $_GET['show_completed'];
    }

    if (!in_array($result, [0, 1])) {
        $result = 0;
    }

    return $result;
}
