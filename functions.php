<?php
define('TASKS_FILTER_TODAY', 'today');
define('TASKS_FILTER_TOMORROW', 'tomorrow');
define('TASKS_FILTER_EXPIRED', 'expired');

// используемый алгоритм хеширования пароля
define('PASSWORD_HASH_ALGO', PASSWORD_DEFAULT);

/**
 * @deprecated Подсчитывает количество задач для переданного проекта
 *
 * @param array $tasks_list Список задач
 * @param string $project_name Название проекта
 *
 * @return int Количество задач проекта
 */
function number_project_tasks(array $tasks_list, string $project_name): int
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
 * @return mixed Количество оставшихся часов или null, если задача бессрочная
 */
function hours_left_deadline(date $date_completion)
{
    if (is_null($date_completion)) {
        return null;
    }

    $task_finish = strtotime($date_completion);
    $seconds_left = $task_finish - time();
    $hours_left = floor($seconds_left/3600);

    return $hours_left;
}


/**
 * Возвращает дату в европейском формате (dd.mm.yyyy)
 *
 * @param string $dt Преобразуемая дата
 *
 * @return string Отформатированная дата
 */
function euro_date(string $dt): string
{
    return (empty($dt)) ? '' : date("d.m.Y", strtotime($dt));
}


/**
 * Добавляет дополнительные классы для задач,
 * у которых истекает срок выполнения и для выполненных задач
 *
 * @param array $task Список задач
 * @param bool $is_show_complete_tasks Признак, показывать ли выполненные задачи
 *
 * @return string Название класса
 */
function additional_task_classes(array $task, bool $is_show_complete_tasks): string
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
 * Устанавливает в запросе параметр project_id с указанным ID проекта
 *
 * @param int $project_id ID проекта
 *
 * @return string Строка запроса
 */
function set_project_query(int $project_id): string
{
    $param_name = 'project_id';
    $param_value = (string)$project_id;

    return modify_query($param_name, $param_value);
}


/**
 * Добавляет дополнительный класс для выделения в меню активного проекта
 * если в GET-запросе присутствует project_id со значением равным переданному
 *
 * @param int $project_id Проверяемый ID проекта
 *
 * @return string Название класса
 */
function mark_active_project(int $project_id): string
{
    return (isset($_GET['project_id']) and ((int)$_GET['project_id'] === $project_id)) ? 'main-navigation__list-item--active' : '';
}


/**
 * Устанавливает или удаляет в запросе указанный параметр param_name
 *
 * @param array $query_data Обрабатываемый массив параметров
 * @param string $param_name Имя параметра
 * @param string $param_value Устанавливаемое значение (необязательный)
 *
 * @return string Строка запроса
 */
function modify_query_data(array $query_data, string $param_name, ?string $param_value = null): string
{
    // принудительно удаляем параметр search из массива (если он есть)
    unset($query_data['search']);
    // удаляем параметр, переданные в $param_name из массива
    unset($query_data[$param_name]);

    if (!is_null($param_value)) {
        $query_data[$param_name] = $param_value;
    }

    return '?' . http_build_query($query_data);
}

/**
 * Устанавливает или удаляет в GET-запросе указанный параметр param_name
 *
 * @param string $param_name Имя параметра
 * @param string $param_value Устанавливаемое значение (необязательный)
 *
 * @return string Строка запроса
 */
function modify_query(string $param_name, ?string $param_value = null): string
{
    return modify_query_data($_GET, $param_name, $param_value);
}

/**
 * Устанавливает значение параметра filter
 *
 * @param string $filter_value Устанавливаемое значение (необязательный)
 *
 * @return string Строка запроса
 */
function set_tasks_query_filter(?string $filter_value = null): string
{
    $param_name = 'filter';

    return modify_query($param_name, $filter_value);
}

/**
 * Получает параметры запроса для всех задач
 *
 * @return string Строка запроса
 */
function get_all_tasks_query_filter(): string
{
    return set_tasks_query_filter();
}

/**
 * Получает параметры запроса для задач на сегодня
 *
 * @return string Строка запроса
 */
function get_tasks_filter_query_for_today(): string
{
    return set_tasks_query_filter(TASKS_FILTER_TODAY);
}

/**
 * Получает параметры запроса для задач на завтра
 *
 * @return string Строка запроса
 */
function get_tasks_filter_query_for_tomorrow(): string
{
    return set_tasks_query_filter(TASKS_FILTER_TOMORROW);
}

/**
 * Получает параметры запроса для просроченных задач
 *
 * @return string Строка запроса
 */
function get_tasks_filter_query_for_expired(): string
{
    return set_tasks_query_filter(TASKS_FILTER_EXPIRED);
}


/**
 * Добавляет дополнительный класс для выделения активного фильтра задач,
 * если в GET-запросе присутствует filter со значением равным переданному
 *
 * @param string $filter_name Проверяемое значение фильтра
 *
 * @return string Название класса
 */
function mark_active_exist_filter_tasks(string $filter_name): string
{
    return (isset($_GET['filter']) && $_GET['filter'] === $filter_name) ? 'tasks-switch__item--active' : '';
}


/**
 * Добавляет дополнительный класс для выделения активного фильтра задач,
 * если в GET-запросе отсутствует filter
 *
 * @return string Название класса
 */
function mark_active_no_filter_tasks(): string
{
    return (!isset($_GET['filter'])) ? 'tasks-switch__item--active' : '';
}


/**
 * Определяет, показывать или нет выполненные задачи в зависимости от переданного GET-запроса
 *
 * @return int Значение 1 - показывать, 0 - не показывать
 */
function show_completed(): int
{
    $result = 0;

    if (isset($_GET['show_completed']) && is_numeric($_GET['show_completed'])) {
        $result = $_GET['show_completed'];

        if (!in_array($result, [0, 1])) {
            $result = 0;
        }
    }

    return $result;
}


/**
 * Отправляет письмо, используя библиотеку SwiftMailer
 *
 * @param Swift_Mailer $mailer SwiftMailer
 * @param string $from e-mail отправителя
 * @param string $to e-mail получателя
 * @param string $name Имя получателя
 * @param string $msg Текст сообщения
 *
 * @return void отсутствует
 */
function mail_sender(Swift_Mailer $mailer, string $from, string $to, string $name, string $msg): void
{
    // Create a message
    $message = (new Swift_Message('Уведомление от сервиса «Дела в порядке»'))
        ->setFrom([$from => 'DoingsDone'])
        ->setTo([$to => $name])
        ->setBody($msg, 'text/html')
    ;

    // Send the message
    $mailer->send($message);
}
