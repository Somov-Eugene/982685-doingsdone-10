<?php
/**
 * Проверяет поле в массиве $_POST и возвращает его значение, если поле существует
 *
 * @param string $name Название поля
 *
 * @return string Значение поля, если оно существует или пустую строку в противном случае
 */
function get_post_value($name) {
    return $_POST($name) ?? '';
}

/**
 * Проверяет email на корректность
 *
 * @param string $name Название поля
 *
 * @return string Сообщение об ошибке, если значение поля некорректное или null, если ошибки нет
 */
function validate_email($name) {
    if (!filter_input(INPUT_POST, $name, FILTER_VALIDATE_EMAIL)) {
        return "Введите корректный email";
    }

    return null;
}

/**
 * Проверяет заполненность поля
 *
 * @param string $name Название поля
 *
 * @return string Сообщение об ошибке, если значение поля некорректное или null, если ошибки нет
 */
function validate_filled($name) {
    if (empty($_POST[$name])) {
        return "Это поле должно быть заполнено";
    }

    return null;
}

/**
 * Проверяет длину введенного значения
 *
 * @param string $name Название поля
 *
 * @return string Сообщение об ошибке, если значение поля некорректное или null, если ошибки нет
 */
function validate_length($name, $min, $max) {
    $len = strlen(get_post_value($name));

    if ($len < $min or $len > $max) {
        return "Значение должно быть от $min до $max символов";
    }

    return null;
}

/**
 * Проверяет допустимость и формат даты
 *
 * @param string $name Название поля
 *
 * @return string Сообщение об ошибке, если значение поля некорректное или null, если ошибки нет
 */
function validate_date($name) {
    if (!is_date_valid($name)) {
        return "Дата не соответствует требуемому формату или несуществующая";
    }

    $current_date = date_create('now');
    $post_date = date_create(get_post_value($name));

    if ($post_date < $current_date) {
        return "Дата должна быть больше или равна текущей";
    }

    return null;
}

/**
 * Проверяет идентификатор выбранного проекта, что он ссылается на реально существующий проект
 *
 * @param string $name Название поля
 *
 * @return string Сообщение об ошибке, если значение поля некорректное или null, если ошибки нет
 */
function validate_project($name, $allowed_list) {
    $id = get_post_value($name);

    if (!in_array($id, $allowed_list)) {
        return "Указан несуществующий проект";
    }

    return null;
}

/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 *
 * Примеры использования:
 * is_date_valid('2019-01-01'); // true
 * is_date_valid('2016-02-29'); // true
 * is_date_valid('2019-04-31'); // false
 * is_date_valid('10.10.2010'); // false
 * is_date_valid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */
function is_date_valid(string $date) : bool {
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false && array_sum(date_get_last_errors()) === 0;
}

/**
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     get_noun_plural_form(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественнго числа
 */
function get_noun_plural_form (int $number, string $one, string $two, string $many): string
{
    $number = (int) $number;
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    switch (true) {
        case ($mod100 >= 11 && $mod100 <= 20):
            return $many;

        case ($mod10 > 5):
            return $many;

        case ($mod10 === 1):
            return $one;

        case ($mod10 >= 2 && $mod10 <= 4):
            return $two;

        default:
            return $many;
    }
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function include_template($name, array $data = []) {
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}
