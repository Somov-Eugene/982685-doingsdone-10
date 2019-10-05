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
function db_get_prepare_stmt(mysqli $link, string $sql, array $data = []): mysqli_stmt
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
function db_fetch_data(mysqli $link, string $sql, array $data = []): array
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
function db_insert_data(mysqli $link, string $sql, array $data = [])
{
    $stmt = db_get_prepare_stmt($link, $sql, $data);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        $result = mysqli_insert_id($link);
    }

    return $result;
}


/**
 * Возвращает учетные данные пользователя
 *
 * @param mysqli $link Ресурс соединения
 * @param string $email e-mail пользователя
 *
 * @return array Данные пользователя или пустой массив, если такой пользователь не найден
 */
function get_user_by_email(mysqli $link, string $email): array
{
    $sql = "
        SELECT *
        FROM users u
        WHERE u.`email` = ? LIMIT 1
    ";

    return db_fetch_data($link, $sql, [$email]);
}


/**
 * Возвращает список проектов указанного пользователя
 * и количество незавершенных задач в каждом из проектов
 *
 * @param mysqli $link Ресурс соединения
 * @param int $user_id ID пользователя
 *
 * @return array Список проектов пользователя (ассоциативный массив)
 */
function get_user_projects(mysqli $link, int $user_id): array
{
    $sql = "
        SELECT
          p.`id`,
          p.`name`,
          COUNT(t.`id`) AS cnt_tasks
        FROM projects p
        LEFT JOIN tasks t ON t.`project_id` = p.`id` AND t.`is_completed` = 0
        WHERE p.`user_id` = ?
        GROUP BY p.`id`
    ";

    return db_fetch_data($link, $sql, [$user_id]);
}


/**
 * Возвращает список задач указанного пользователя
 * в зависимости от переданных параметров
 *
 * @param mysqli $link Ресурс соединения
 * @param int $user_id ID пользователя
 * @param int $project_id ID проекта
 * @param string $filter Название фильтра задач
 * @param string $search Поисковый запрос
 *
 * @return array Список задач пользователя (ассоциативный массив)
 */
function get_user_tasks(mysqli $link, int $user_id, ?int $project_id = null, ?string $filter = null, ?string $search = null): array
{
    $params = [$user_id, $user_id];
    $where = 't.`user_id` = ?';

    if (!is_null($project_id)) {
        $params[] = $project_id;
        $where .= ' AND p.`id` = ?';
    }

    if (!is_null($filter)) {
        switch ($filter) {
            case TASKS_FILTER_TODAY:
                $where .= ' AND t.`dt_completion` = CURDATE()';
                break;
            case TASKS_FILTER_TOMORROW:
                $where .= ' AND t.`dt_completion` = DATE_ADD(CURDATE(), INTERVAL 1 DAY)';
                break;
            case TASKS_FILTER_EXPIRED:
                $where .= ' AND t.`dt_completion` < CURDATE() AND t.`is_completed` = 0';
                break;
        }
    }

    if (!is_null($search)) {
        $params[] = $search;
        $where .= ' AND MATCH(t.`name`) AGAINST (?)';
    }

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
        WHERE {$where}
        ORDER BY t.`dt_add` DESC
    ";

    return db_fetch_data($link, $sql, $params);
}


/**
 * Определяет, имеется ли у данного пользователя проект c указанным ID
 *
 * @param mysqli $link Ресурс соединения
 * @param int $user_id ID пользователя
 * @param int $project_id ID проверяемого проекта
 *
 * @return bool Логическое значение (true/false)
 */
function is_exist_project(mysqli $link, int $user_id, int $project_id): bool
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
 * @return bool Логическое значение (true/false)
 */
function is_exist_user(mysqli $link, string $email): bool
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
 * Изменяет состояние задачи (выполнена/не выполнена) на противоположное
 *
 * @param mysqli $link Ресурс соединения
 * @param int $task_id ID задачи
 *
 * @return void Отсутствует
 */
function toggle_state_task(mysqli $link, int $task_id): void
{

    $sql = "
        UPDATE tasks t
        SET t.`is_completed` = (NOT t.`is_completed`)
        WHERE t.`id` = ?
    ";

    db_fetch_data($link, $sql, [$task_id]);
}


/**
 * Возвращает список пользователей, имеющих невыполненные задачи,
 * срок выполнения которых истекает в текущий день
 *
 * @param mysqli $link Ресурс соединения
 *
 * @return array Список пользователей или пустой массив, если такие задачи отсутствуют
 */
function get_users_tasks_expired_today(mysqli $link): array
{
    $sql = "
        SELECT
          u.`id`,
          u.`email`,
          u.`name`
        FROM users u
        JOIN tasks t ON t.`user_id` = u.`id`
        WHERE t.`is_completed` = 0
        AND t.`dt_completion` = CURDATE()
        GROUP BY u.`id`
    ";

    return db_fetch_data($link, $sql, []);
}


/**
 * Возвращает для указанного пользователя список невыполненных задач,
 * срок выполнения которых истекает сегодня
 *
 * @param mysqli $link Ресурс соединения
 * @param int $user_id ID пользователя
 *
 * @return array Список названий задач пользователя
 */
function get_tasks_expired_today_by_user(mysqli $link, int $user_id): array
{
    $sql = "
        SELECT
          t.`name`
        FROM tasks t
        WHERE t.`user_id` = ?
        AND t.`is_completed` = 0
        AND t.`dt_completion` = CURDATE()
        ORDER BY t.`dt_add` DESC
    ";

    return db_fetch_data($link, $sql, [$user_id]);
}
