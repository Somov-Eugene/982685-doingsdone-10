<?php
require_once 'helpers.php';
require_once 'functions.php';
require_once 'database.php';

$page_title = "Дела в порядке - Добавление задачи";
$user_name = "Константин";
$user_email = 'kkk@gmail.com';
$user_id = 0;

$projects = [];
$project_ids = [];

// подключение к MySQL
$db_link = db_init('localhost', 'root', '', '982685-doingsdone-10');

if (!$db_link) {
    // oшибка подключения к БД
    $errorMsg = 'Ошибка подключения к БД. Дальнейшая работа сайта невозможна!';
    exit($errorMsg);
}

// ОК: cоединение установлено

// получение ID текущего пользователя
$user = get_user_by_email($db_link, $user_email);
if ($user) {
    $user_id = $user["id"];
}

// получение списка проектов текущего пользователя
$projects = get_user_projects($db_link, $user_id);
$project_ids = array_column($projects, 'id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // форма отправлена

    // сохраняем переданные поля формы
    $task = [
        'name' => get_post_value('name'),
        'project' => get_post_value('project'),
        'date' => empty($_POST['date']) ? null : get_post_value('date'),
        'file' => get_post_value('file')
    ];

    // обязательные к заполнению поля формы
    $required_fields = ['name', 'project'];

    // правила валидации полей
    $rules = [
        'name' => function () {
            return validate_filled('name');
        },
        'project' => function () use ($project_ids) {
            return validate_project('project', $project_ids);
        },
        'date' => function () {
            return validate_date('date');
        }
    ];

    // ошибки валидации
    $errors = [];

    foreach ($task as $key => $value) {
        // для каждого поля проверяем,
        // есть ли для этого поля правило валидации
        if ( isset($rules[$key]) ) {
            // получаем функцию валидации и затем вызываем ее
            $rule = $rules[$key];
            // возможные ошибки сохраняем в массиве $errors
            $errors[$key] = $rule();
        }
    }

    // фильтруем массив, удаляя из него null
    $errors = array_filter($errors);

    // проверка на пустое значение
    foreach($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = 'Это поле требуется заполнить';
        }
    }

    // если к задаче был прикреплен файл, то проверяем загружен ли файл
    if (isset($_FILES['file']['error']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        // загружаем файл, если нет других ошибок валидации
        // если же они есть, то отменяем загрузку файла
        if (count($errors)) {
            $errors['file'] = 'Файл может быть отправлен только после заполнения всех обязательных полей';
        } else {
            // переносим файл в публичную директорию и сохраняем ссылку
            $temp_name = $_FILES['file']['tmp_name'];
            $file_name = uniqid('dd_') . $_FILES['file']['name'];
            $file_path = __DIR__ . '/uploads/';
            $file_url = '/uploads/' . $file_name;
            $task['file'] = $file_name;

            move_uploaded_file($temp_name, $file_path . $file_name);
        }
    } else if (isset($_FILES['file']['error']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors['file'] = 'Не удалось загрузить файл';
    }

    // если есть ошибки валидации, то показать их
    if (count($errors)) {
        $main_content = include_template(
            'form-task.php',
            [
                'task' => $task,
                'errors' => $errors,
                'projects' => $projects
            ]
        );
    }
    else {
        // ошибок нет - добавляем новую запись в БД
        $sql = "INSERT INTO tasks (`name`, `file`, `dt_completion`, `user_id`, `project_id`) VALUES (?, ?, ?, ?, ?)";
        $task_id = db_insert_data($db_link, $sql, [ $task['name'], $task['file'], $task['date'], $user_id, $task['project'] ]);

        if (empty($task_id)) {
            $errorMsg = 'Ошибка при добавлении новой задачи';
            die($errorMsg);
        }

        // При успешном сохранении формы, переадресовывать пользователя на главную страницу
        header("Location: index.php");
    }
}
else {
    // форма не отправлена - показать пустую форму
    $main_content = include_template(
        'form-task.php',
        [
            'projects' => $projects
        ]
    );
}

$layout_content = include_template(
    'layout.php',
    [
	    'main_content' => $main_content,
        'page_title'=> $page_title,
        'user_name' => $user_name
    ]
);

print($layout_content);
