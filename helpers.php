<?php
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
function is_date_valid(string $date) : bool
{
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
function get_noun_plural_form (int $number, string $one, string $two, string $many) : string
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
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML-контент
 *
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 *
 * @return string Итоговый HTML
 */
function include_template($name, array $data = []) : string
{
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

/**
 * Проверяет поле в массиве $_POST и возвращает его значение, если поле существует
 *
 * @param string $field Название поля
 *
 * @return string Значение поля, если оно существует или пустую строку в противном случае
 */
function get_post_value($field)
{
    return $_POST[$field] ?? '';
}

/**
 * Проверяет email на корректность
 *
 * @param array $check Ассоциативный массив, в котором требуется проверить значение
 * @param string $field Название поля (по умолчанию 'email')
 *
 * @return mixed Сообщение об ошибке, если значение поля некорректное или null, если ошибки нет
 */
function validate_email($check, $field = 'email')
{
    if (!filter_var($check[$field], FILTER_VALIDATE_EMAIL)) {
        return 'Введите корректный e-mail';
    }

    return null;
}

/**
 * Проверяет заполненность поля
 *
 * @param array $check Ассоциативный массив, в котором требуется проверить значение
 * @param string $field Название поля
 *
 * @return mixed Сообщение об ошибке, если значение поля некорректное или null, если ошибки нет
 */
function validate_filled($check, $field)
{
    if (empty($check[$field])) {
        return 'Это поле должно быть заполнено';
    }

    return null;
}

/**
 * Проверяет допустимость и формат даты
 *
 * @param array $check Ассоциативный массив, в котором требуется проверить значение
 * @param string $field Название поля
 *
 * @return mixed Сообщение об ошибке, если значение поля некорректное или null, если ошибки нет
 */
function validate_date($check, $field)
{
    if (!is_date_valid($check[$field])) {
        return 'Дата не соответствует требуемому формату или несуществующая';
    }

    $current_date = strtotime(date('Y-m-d'));
    $checked_date = strtotime($check[$field]);

    if ($checked_date < $current_date) {
        return 'Дата должна быть больше или равна текущей';
    }


    return null;
}

/**
 * Проверяет идентификатор выбранного проекта, что он ссылается на реально существующий проект
 *
 * @param array $allowed_list Массив с допустимыми ID
 * @param int $id Проверяемый ID проекта
 *
 * @return mixed Сообщение об ошибке, если значение поля некорректное или null, если ошибки нет
 */
function validate_project($allowed_list, $id)
{
    if (!in_array($id, $allowed_list)) {
        return 'Указан несуществующий проект';
    }

    return null;
}
