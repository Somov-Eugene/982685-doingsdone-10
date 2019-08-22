<?php
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
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt($link, $sql, $data = []) {
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            }
            else if (is_string($value)) {
                $type = 's';
            }
            else if (is_double($value)) {
                $type = 'd';
            }

            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
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
 * Возвращает учетные данные текущего пользователя
 *
 * @param $link mysqli Ресурс соединения
 * @param $user_name string e-mail пользователя
 *
 * @return array Данные пользователя (ассоциативный массив)
 */
function get_user_by_email($link, $email) {
    $result = [];

    $sql = "SELECT * FROM users u WHERE u.`email` = ?";
    $sql_result = db_fetch_data($link, $sql, [$email]);

    if ($sql_result) {
        $result = $sql_result;
    }

    return $result;
}


/**
 * Возвращает список проектов переданного пользователя
 *
 * @param $link mysqli Ресурс соединения
 * @param $user_id int ID пользователя
 *
 * @return array Список проектов пользователя (ассоциативный массив)
 */
function get_user_projects($link, $user_id) {
    $result = [];

    $sql = "SELECT p.`name` FROM projects p WHERE p.`user_id` = ?";
    $sql_result = db_fetch_data($link, $sql, [$user_id]);

    if ($sql_result) {
        $result = $sql_result;
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
function get_user_tasks($link, $user_id) {
    $result = [];

    $sql = "
        SELECT
          t.`is_completed`,
          t.`name`,
          t.`dt_completion` AS date_completion,
          p.`name` AS project_name
        FROM tasks t
        JOIN projects p ON p.`id` = t.`project_id` AND p.`user_id` = ?
        WHERE t.`user_id` = ?
        ";
    $sql_result = db_fetch_data($link, $sql, [$user_id, $user_id]);

    if ($sql_result) {
        $result = $sql_result;
    }

    return $result;
}
