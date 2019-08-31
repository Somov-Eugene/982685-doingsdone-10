<?php
/*
3.2. Создание новой задачи
Страница доступна только аутентифицированным пользователям.

Выполняется после заполнения формы на странице «Добавление задачи».

Последовательность действий:
1. Проверить, что отправлена форма.
2. Убедиться, что заполнены все обязательные поля.
3. Выполнить все проверки.
4. Если есть ошибки заполнения формы, то сохранить их в отдельном массиве.
5. Если ошибок нет, то сохранить новую задачу (учитывая выбранный проект).
6. Если к задаче был прикреплен файл, то перенести его в публичную директорию и сохранить ссылку.
При успешном сохранении формы, переадресовывать пользователя на главную страницу.

Список проверок
-- Проверка даты
   - Содержимое поля «дата завершения» должно быть датой в формате «ГГГГ-ММ-ДД»;
   - Эта дата должна быть больше или равна текущей.

-- Проверка проекта
   - Для идентификатора выбранного проекта проверять, что он ссылается на реально существующий проект.

-- Проверка имени задачи
   - Имя задачи не должно быть пустой строкой.
*/
