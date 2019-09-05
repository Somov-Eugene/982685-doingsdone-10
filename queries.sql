/*
// Добавляем пользователей
*/
INSERT INTO users (`email`, `username`, `password`)
VALUES
  ('kkk@gmail.com', 'Константин', '$2y$10$oujcOXU1d9CiDbibbbT.VOnnpMcWL/l.dIHLp2.v2nSB3jQX78nwq'),  -- password1
  ('manunya123@mail.ru', 'Мария', '$2y$10$WGEkqlTSuxjeeqVXyUj89edthpiPAG3A81R/UjUXAz4Mad6mQm9FO'),  -- password2
  ('fe2000@yandex.ru', 'Фёдор', '$2y$10$xr6m/4OA8Zxw2lrkt/k8vO7GT4B4esbegOqL10BtdxGcLCP2dbR02');    -- password3


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
