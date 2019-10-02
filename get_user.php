<?php
session_start();

$user = [];

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
}
