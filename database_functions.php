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
 * @return mixed ID добавленной записи
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
        // одна уникальная запись
        $result = $sql_result[0];
    }

    return $result;
}


/**
 * Возвращает список проектов переданного пользователя и количество задач в каждом из проектов
 *
 * @param $link mysqli Ресурс соединения
 * @param $user_id int ID пользователя
 *
 * @return array Список проектов пользователя (ассоциативный массив)
 */
function get_user_projects($link, $user_id) {
    $result = [];

    $sql = "
        SELECT
          p.`id`,
          p.`name`,
          COUNT(t.`id`) AS cnt_tasks
        FROM projects p
        LEFT JOIN tasks t ON t.`project_id` = p.`id`
        WHERE p.`user_id` = ?
        GROUP BY p.`id`
        ";
    $sql_result = db_fetch_data($link, $sql, [$user_id]);

    if ($sql_result) {
        $result = $sql_result;
    }

    return $result;
}


/**
 * Возвращает список всех задач переданного пользователя
 *
 * @param $link mysqli Ресурс соединения
 * @param $user_id int ID пользователя
 *
 * @return array Список задач пользователя (ассоциативный массив)
 */
function get_user_tasks_all($link, $user_id) {
    $result = [];

    $sql = "
        SELECT
          t.`is_completed`,
          t.`name`,
          t.`dt_completion` AS date_completion,
          t.`file`,
          p.`name` AS project_name
        FROM tasks t
        JOIN projects p ON p.`id` = t.`project_id` AND p.`user_id` = ?
        WHERE t.`user_id` = ?
        ORDER BY t.`dt_add` DESC";
    $sql_result = db_fetch_data($link, $sql, [$user_id, $user_id]);

    if ($sql_result) {
        $result = $sql_result;
    }

    return $result;
}


/**
 * Возвращает список задач пользователя по указанному проекту
 *
 * @param $link mysqli Ресурс соединения
 * @param $user_id int ID пользователя
 * @param $project_id int ID проекта
 *
 * @return array Список задач пользователя (ассоциативный массив)
 */
function get_user_tasks_project($link, $user_id, $project_id) {
    $result = [];

    $sql = "
        SELECT
          t.`is_completed`,
          t.`name`,
          t.`dt_completion` AS date_completion,
          t.`file`,
          p.`name` AS project_name
        FROM tasks t
        JOIN projects p ON p.`id` = t.`project_id` AND p.`user_id` = ?
        WHERE t.`user_id` = ?
        AND p.`id` = ?
        ORDER BY t.`dt_add` DESC";
    $sql_result = db_fetch_data($link, $sql, [$user_id, $user_id, $project_id]);

    if ($sql_result) {
        $result = $sql_result;
    }

    return $result;
}


/**
 * Определяет имется ли переданный ID проекта у данного пользователя
 *
 * @param $link mysqli Ресурс соединения
 * @param $user_id int ID пользователя
 * @param $id_project_id int ID проверяемого проекта
 * @return boolean Логическое значение (true/false)
 */
function is_exist_project(mysqli $link, int $user_id, int $project_id) {
    $result = false;

    $sql = "SELECT count(*) AS cnt FROM projects p WHERE p.`user_id` = ? AND p.`id` = ?";
    $sql_result = db_fetch_data($link, $sql, [$user_id, $project_id]);

    if ($sql_result) {
        $result = ($sql_result[0]['cnt'] !== 0) ? true : false;
    }

    return $result;
}


/**
 * Добавляет задачу для указанного пользователя
 *
 * @param $link mysqli Ресурс соединения
 * @param $new_task array Массив с параметрами задачи
 *
 * @return mixed ID добавленной записи
 */
function add_user_task($link, $new_task) {
    $result = null;

    $data = [
        $new_task['name'],
        $new_task['file'],
        $new_task['date'],
        $new_task['user_id'],
        $new_task['project']
    ];

    $sql = "INSERT INTO tasks (`name`, `file`, `dt_completion`, `user_id`, `project_id`) VALUES (?, ?, ?, ?, ?)";
    $insert_id = db_insert_data($link, $sql, $data);

    if ($insert_id) {
        $result = $insert_id;
    }

    return $result;
}


/**
 * Определяет, имется ли переданный e-mail в БД пользователей
 *
 * @param $link mysqli Ресурс соединения
 * @param $email string Проверяемый e-mail
 * @return boolean Логическое значение (true/false)
 */
function is_exist_user(mysqli $link, string $email) {
    $result = get_user_by_email($link, $email);

    return (empty($result)) ? false : true;
}


/**
 * Добавляет нового пользователя
 *
 * @param $link mysqli Ресурс соединения
 * @param $new_user array Массив с параметрами пользователя
 * @return mixed ID добавленной записи
 */
function register_user($link, $new_user) {
    $result = null;

    $data = [
        $new_user['email'],
        $new_user['name'],
        $new_user['password']
    ];

    $sql = "INSERT INTO users (`email`, `username`, `password`) VALUES (?, ?, ?)";
    $insert_id = db_insert_data($link, $sql, $data);

    if ($insert_id) {
        $result = $insert_id;
    }

    return $result;
}
