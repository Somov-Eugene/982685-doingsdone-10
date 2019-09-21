<?php
require_once 'init.php';

$send_users = get_users_tasks_expired_today($link);

foreach ($send_users as $key => $recipient) {
    $msg_html = '<p><strong>Уважаемый(-ая) ' . $recipient['name'] . '!</strong></p>';
    $msg_html .= '<p>На ' . $recipient['date_completion'] . ' у Вас ';

    $tasks = get_user_tasks_today($link, $recipient['id']);
    $tasks_name = array_column($tasks, 'name');

    $msg_html .= (count($tasks_name) === 1) ? 'запланирована задача «' . $tasks_name[0] : 'запланированы задачи: «'. implode('», «', $tasks_name);
    $msg_html .= '»</p>';

    mail_sender($recipient['email'], $recipient['name'], $msg_html);
}
