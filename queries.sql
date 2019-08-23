/*
// Добавляем пользователей
*/
INSERT INTO users (`email`, `username`, `password`)
VALUES
  ('kkk@gmail.com', 'Константин', 'password1'),
  ('manunya123@mail.ru', 'Мария', 'password2'),
  ('fe2000@yandex.ru', 'Фёдор', 'password3');


/*
// Добавляем проекты (для первого пользователя)
*/
INSERT INTO projects (`name`, `user_id`)
VALUES
  ('Входящие', 1),
  ('Учеба', 1),
  ('Работа', 1),
  ('Домашние дела', 1),
  ('Авто', 1);


/*
// Добавляем задачи (для первого пользователя)
*/
INSERT INTO tasks (`is_completed`, `name`, `dt_completion`, `user_id`, `project_id`)
VALUES
  (0, 'Собеседование в IT компании', '2018-12-01', 1, 3),
  (0, 'Выполнить тестовое задание', '2018-12-25', 1, 3),
  (1, 'Сделать задание первого раздела', '2018-12-21', 1, 2),
  (0, 'Встреча с другом', '2018-12-22', 1, 1),
  (0, 'Купить корм для кота', null, 1, 4),
  (0, 'Заказать пиццу', null, 1, 4);


/*
// получить список из всех проектов для одного пользователя
*/
SELECT * FROM projects p WHERE p.`user_id` = $userID;


/*
// получить список из всех задач для одного проекта
*/
SELECT * FROM tasks t WHERE t.`project_id` = $projectID;


/*
// пометить задачу как выполненную
*/
UPDATE tasks t
SET t.`is_completed` = 1
WHERE t.`id` = $taskID;


/*
// обновить название задачи по её идентификатору
*/
UPDATE tasks t
SET t.`name` = $new_task_name
WHERE t.`id` = $taskID;
