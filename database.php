<?php
require_once 'helpers.php';

/**
 * Создает новое подключение к БД и настраивает параметры подключения
 *
 * @param $host string Имя хоста БД
 * @param $user string Имя пользователя БД
 * @param $password string Пароль БД
 * @param $database string Название БД
 *
 * @return mysqli Ресурс соединения или false в случае ошибки
 */
function db_init($host, $user, $password, $database) {
    $link = mysqli_connect($host, $user, $password, $database);

    if ($link) {
        // ОК: cоединение установлено

        // установка кодировки
        mysqli_set_charset($link, 'utf8');

        // включить преобразование типов для INT и FLOAT
        mysqli_options($link, MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
    }

    return $link;
}


/**
 * Возвращает результат выполнения SELECT-запроса
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return array Результат выполнения запроса в виде ассоциативного массива или пустой массив в случае ошибки
 */
function db_fetch_data($link, $sql, $data = []) {
    $result = [];

    $stmt = db_get_prepare_stmt($link, $sql, $data);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($res) {
        $result = mysqli_fetch_all($res, MYSQLI_ASSOC);
    }

    return $result;
}


/**
 * Добавляет в БД новую запись и возвращает ID этой записи
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return string ID добавленной записи
 */
function db_insert_data($link, $sql, $data = []) {
    $stmt = db_get_prepare_stmt($link, $sql, $data);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        $result = mysqli_insert_id($link);
    }

    return $result;
}


/**
 * Возвращает ID текущего пользователя
 *
 * @param $link mysqli Ресурс соединения
 * @param $user_name string Имя пользователя
 *
 * @return int ID пользователя
 */
function db_get_id_user($link, $user_name) {
    $user_id = 0;

    $sql = "SELECT u.`id` FROM users u WHERE u.`username` = ?";
    $sql_result = db_fetch_data($link, $sql, [$user_name]);

    if ($sql_result) {
        $user_id = $sql_result[0]['id'];
    }

    return $user_id;
}


/**
 * Возвращает список проектов переданного пользователя
 *
 * @param $link mysqli Ресурс соединения
 * @param $user_id int ID пользователя
 *
 * @return array Список названий проектов пользователя (простой массив)
 */
function db_get_projects_list($link, $user_id) {
    $result = [];

    $sql = "SELECT p.`name` FROM projects p WHERE p.`user_id` = ?";
    $sql_result = db_fetch_data($link, $sql, [$user_id]);

    if ($sql_result) {
        // делаем простой массив из ассоциативного
        foreach($sql_result as $row) {
            $result[] = $row['name'];
        }
    }

    return $result;
}


/**
 * Возвращает список задач переданного пользователя
 *
 * @param $link mysqli Ресурс соединения
 * @param $user_id int ID пользователя
 *
 * @return array Список задач пользователя (ассоциативный массив)
 */
function db_get_tasks_list($link, $user_id) {
    $result = [];

    $sql = "SELECT"
         ."   t.`is_completed`,"
         ."   t.`name`,"
         ."   t.`dt_completion` AS date_completion,"
         ."   p.`name` AS project_name"
         ." FROM tasks t"
         ." JOIN projects p ON p.`id` = t.`project_id` AND p.`user_id` = ?"
         ." WHERE t.`user_id` = ?";
    $sql_result = db_fetch_data($link, $sql, [$user_id, $user_id]);

    if ($sql_result) {
        $result = $sql_result;
    }

    return $result;
}
