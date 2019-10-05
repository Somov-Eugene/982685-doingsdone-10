<?php
require_once 'vendor/autoload.php';

if (!file_exists("config/smtp.php")) {
    echo 'Добавьте файл конфигурации подключения к SMTP-серверу: config/smtp.php';
    exit;
}

require_once "config/smtp.php";

// Create the Transport
$transport = (new Swift_SmtpTransport($smtp_host, $smtp_port))
    ->setUsername($smtp_user)
    ->setPassword($smtp_password)
;

// Create the Mailer using your created Transport
$mailer = new Swift_Mailer($transport);
