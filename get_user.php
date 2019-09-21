<?php
session_start();

// получение данных пользователя из сессии
if (isset($_SESSION['user'])) {
    $is_anonymous = false;

    $user = $_SESSION['user'];
} else {
    // в сессии нет данных - анонимный пользователь
    $is_anonymous = true;

    $user = [
        'id' => '',
        'name' => '',
        'email' => '',
        'password' => ''
    ];
}
