<?php
require_once 'init.php';

$send_users = get_users_tasks_expired_today($link);

foreach ($send_users as $key => $recipient) {
    $date = euro_date($recipient['date_completion']);

    $tasks = get_user_tasks($link, $recipient['id'], null, TASKS_FILTER_TODAY);
    $tasks_name = array_column($tasks, 'name');
    $tasks_names = '«' . implode('», «', $tasks_name) . '»';

    $msg_html = "
        <p>
            <strong>Уважаемый(-ая) {$recipient['name']}!</strong>
        </p>
        <p>На {$date} у Вас запланированы следующие задачи: {$tasks_names}</p>
    ";

    mail_sender($mailer, $from, $recipient['email'], $recipient['name'], $msg_html);
}
