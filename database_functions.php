<?php
/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param mysqli $link Ресурс соединения
 * @param string $sql SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt($link, $sql, $data = [])
{
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
 * Возвращает результат выполнения запроса
 *
 * @param mysqli $link Ресурс соединения
 * @param string $sql SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return array Результат выполнения запроса в виде ассоциативного массива или пустой массив в случае ошибки
 */
function db_fetch_data($link, $sql, $data = [])
{
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
 * @param mysqli $link Ресурс соединения
 * @param string $sql SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mixed ID добавленной записи или null, если произошла ошибка
 */
function db_insert_data($link, $sql, $data = [])
{
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
 * @param mysqli $link Ресурс соединения
 * @param string $user_name e-mail пользователя
 *
 * @return array Данные пользователя (ассоциативный массив)
 */
function get_user_by_email($link, $email)
{
    $sql = "
        SELECT *
        FROM users u
        WHERE u.`email` = ?
    ";

    return db_fetch_data($link, $sql, [$email]);
}


/**
 * Возвращает список проектов переданного пользователя и количество задач в каждом из проектов
 *
 * @param mysqli $link Ресурс соединения
 * @param int $user_id ID пользователя
 *
 * @return array Список проектов пользователя (ассоциативный массив)
 */
function get_user_projects($link, $user_id)
{
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

    return db_fetch_data($link, $sql, [$user_id]);
}


/**
 * Возвращает список всех задач переданного пользователя
 *
 * @param mysqli $link Ресурс соединения
 * @param int $user_id ID пользователя
 *
 * @return array Список задач пользователя (ассоциативный массив)
 */
function get_user_tasks_all($link, $user_id)
{
    $sql = "
        SELECT
          t.`id`,
          t.`is_completed`,
          t.`name`,
          t.`dt_completion` AS date_completion,
          t.`file`,
          p.`name` AS project_name
        FROM tasks t
        JOIN projects p ON p.`id` = t.`project_id` AND p.`user_id` = ?
        WHERE t.`user_id` = ?
        ORDER BY t.`dt_add` DESC
    ";

    return db_fetch_data($link, $sql, [$user_id, $user_id]);
}


/**
 * Возвращает список задач пользователя по указанному проекту
 *
 * @param mysqli $link Ресурс соединения
 * @param int $user_id ID пользователя
 * @param int $project_id ID проекта
 *
 * @return array Список задач пользователя (ассоциативный массив)
 */
function get_user_tasks_project($link, $user_id, $project_id)
{
    $sql = "
        SELECT
          t.`id`,
          t.`is_completed`,
          t.`name`,
          t.`dt_completion` AS date_completion,
          t.`file`,
          p.`name` AS project_name
        FROM tasks t
        JOIN projects p ON p.`id` = t.`project_id` AND p.`user_id` = ?
        WHERE t.`user_id` = ?
        AND p.`id` = ?
        ORDER BY t.`dt_add` DESC
    ";

    return db_fetch_data($link, $sql, [$user_id, $user_id, $project_id]);
}


/**
 * Определяет имется ли переданный ID проекта у данного пользователя
 *
 * @param mysqli $link Ресурс соединения
 * @param int $user_id ID пользователя
 * @param int $project_id ID проверяемого проекта
 *
 * @return boolean Логическое значение (true/false)
 */
function is_exist_project(mysqli $link, int $user_id, int $project_id)
{
    $sql = "
        SELECT
          p.`id`
        FROM projects p
        WHERE p.`user_id` = ? AND p.`id` = ? LIMIT 1
    ";
    $rows = db_fetch_data($link, $sql, [$user_id, $project_id]);

    return count($rows) > 0;
}


/**
 * Добавляет задачу для указанного пользователя
 *
 * @param mysqli $link Ресурс соединения
 * @param array $new_task Массив с параметрами задачи
 *
 * @return mixed ID добавленной записи или null, если произошла ошибка
 */
function add_user_task(mysqli $link, array $new_task)
{
    $result = null;

    $data = [
        $new_task['name'],
        $new_task['file'],
        $new_task['date'],
        $new_task['user_id'],
        $new_task['project']
    ];

    $sql = "
        INSERT INTO tasks (`name`, `file`, `dt_completion`, `user_id`, `project_id`)
        VALUES (?, ?, ?, ?, ?)
    ";
    $insert_id = db_insert_data($link, $sql, $data);

    if ($insert_id) {
        $result = $insert_id;
    }

    return $result;
}


/**
 * Добавляет проект для указанного пользователя
 *
 * @param mysqli $link Ресурс соединения
 * @param array $new_project Массив с параметрами проекта
 *
 * @return mixed ID добавленной записи или null, если произошла ошибка
 */
function add_user_project(mysqli $link, array $new_project)
{
    $result = null;

    $data = [
        $new_project['name'],
        $new_project['user_id']
    ];

    $sql = "
        INSERT INTO projects (`name`, `user_id`)
        VALUES (?, ?)
    ";
    $insert_id = db_insert_data($link, $sql, $data);

    if ($insert_id) {
        $result = $insert_id;
    }

    return $result;
}


/**
 * Определяет, имется ли переданный e-mail в БД пользователей
 *
 * @param mysqli $link Ресурс соединения
 * @param string $email Проверяемый e-mail
 *
 * @return boolean Логическое значение (true/false)
 */
function is_exist_user(mysqli $link, string $email)
{
    $result = get_user_by_email($link, $email);

    return (empty($result)) ? false : true;
}


/**
 * Добавляет нового пользователя
 *
 * @param mysqli $link Ресурс соединения
 * @param array $new_user Массив с параметрами пользователя
 *
 * @return mixed ID добавленной записи или null, если произошла ошибка
 */
function register_user(mysqli $link, array $new_user)
{
    $result = null;

    $data = [
        $new_user['email'],
        $new_user['name'],
        $new_user['password']
    ];

    $sql = "
        INSERT INTO users (`email`, `name`, `password`)
        VALUES (?, ?, ?)
    ";
    $insert_id = db_insert_data($link, $sql, $data);

    if ($insert_id) {
        $result = $insert_id;
    }

    return $result;
}


/**
 * Возвращает список задач пользователя по указанному поисковому запросу
 *
 * @param mysqli $link Ресурс соединения
 * @param int $user_id ID пользователя
 * @param string $search Поисковый запрос (FULLTEXT)
 *
 * @return array Список найденных задач пользователя или пустой массив, если ничего не было найдено
 */
function get_user_tasks_ft_search(mysqli $link, int $user_id, string $search)
{
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
        AND MATCH(t.`name`) AGAINST(?)
        ORDER BY t.`dt_add` DESC
    ";

    return db_fetch_data($link, $sql, [$user_id, $user_id, $search]);
}


/**
 * Изменяет состояние задачи (выполнена/не выполнена) на противоположное
 *
 * @param mysqli $link Ресурс соединения
 * @param int $task_id ID задачи
 *
 * @return void Отсутствует
 */
function toggle_state_task(mysqli $link, int $task_id)
{

    $sql = "
        UPDATE tasks t
        SET t.`is_completed` = (NOT t.`is_completed`)
        WHERE t.`id` = ?
        ";

    db_fetch_data($link, $sql, [$task_id]);
}
