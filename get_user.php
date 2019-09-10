<?php
session_start();

// получение данных пользователя из сессии
if (isset($_SESSION['user'])){
    $user = $_SESSION['user'];
} else {
    // в сессии нет данных - анонимный пользователь
    $user = [
        'id' => '',
        'name' => '',
        'email' => '',
        'password' => ''
    ];
}
