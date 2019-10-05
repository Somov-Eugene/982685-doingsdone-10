<?php
require_once 'init.php';

$current_date = date('d.m.Y');

// получаем список пользователей, имеющих задачи, срок выполнения которых истекает сегодня
$send_users = get_users_tasks_expired_today($link);

foreach ($send_users as $key => $recipient) {
    // получаем список незавершенных задач на сегодня
    $tasks = get_tasks_expired_today_by_user($link, $recipient['id']);
    $tasks_name = array_column($tasks, 'name');
    $tasks_list = '«' . implode('», «', $tasks_name) . '»';

    $msg_html = "
        <p>
            <strong>Уважаемый(-ая) {$recipient['name']}!</strong>
        </p>
        <p>На {$current_date} у Вас запланированы следующие задачи: {$tasks_list}</p>
    ";

    mail_sender($mailer, $from, $recipient['email'], $recipient['name'], $msg_html);
}
