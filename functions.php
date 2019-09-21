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
 * если в GET-запросе присутствует project_id со значением равным переданному
 *
 * @param int $project_id Проверяемый ID проекта
 *
 * @return string Название класса
 */
function mark_active_project(int $project_id)
{
    return (isset($_GET['project_id']) and ((int)$_GET['project_id'] === $project_id)) ? 'main-navigation__list-item--active' : '';
}


/**
 * Добавляет дополнительный класс для выделения активного фильтра задач,
 * если в GET-запросе присутствует filter со значением равным переданному
 *
 * @param string $filter_name Проверяемое значение фильтра
 *
 * @return string Название класса
 */
function mark_active_exist_filter_tasks(string $filter_name)
{
    return (isset($_GET['filter']) && $_GET['filter'] === $filter_name) ? 'tasks-switch__item--active' : '';
}


/**
 * Добавляет дополнительный класс для выделения активного фильтра задач,
 * если в GET-запросе отсутствует filter
 *
 * @return string Название класса
 */
function mark_active_no_filter_tasks()
{
    return (!isset($_GET['filter'])) ? 'tasks-switch__item--active' : '';
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

        if (!in_array($result, [0, 1])) {
            $result = 0;
        }
    }

    return $result;
}


/**
 * Отправляет письмо, используя библиотеку SwiftMailer
 *
 * @param string $email e-mail пользователя
 * @param string $username Имя пользователя
 * @param string $msg Текст сообщения
 *
 * @return void отсутствует
 */
function mail_sender(string $email, string $username, string $msg)
{
    // Create the Transport
    $transport = (new Swift_SmtpTransport('phpdemo.ru', 25))
        ->setUsername('keks@phpdemo.ru')
        ->setPassword('htmlacademy')
    ;

    // Create the Mailer using your created Transport
    $mailer = new Swift_Mailer($transport);

    // Create a message
    $message = (new Swift_Message('Уведомление от сервиса «Дела в порядке»'))
        ->setFrom(['keks@phpdemo.ru' => 'DoingsDone'])
        ->setTo([$email => $username])
        ->setBody($msg, 'text/html')
    ;

    // Send the message
    $mailer->send($message);
}
