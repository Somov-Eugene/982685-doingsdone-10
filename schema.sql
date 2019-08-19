CREATE DATABASE doingsdone
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE doingsdone;

/*
// ---------------------------------------------
// Список всех сущностей:
// ---------------------------------------------
//
// = Проект =
// Поля:
//   Состоит только из названия. Каждая задача может быть привязана к одному из проектов.
//   Проект имеет связь с пользователем, который его создал.
// Связи:
//   автор: пользователь, создавший проект;
*/
CREATE TABLE projects (
  id       	INT AUTO_INCREMENT PRIMARY KEY,
  name     	CHAR(64) NOT NULL UNIQUE,
  user_id  	INT NOT NULL
);

CREATE UNIQUE INDEX idx_projects_name ON projects (name);

/*
// = Задача =
// Центральная сущность всего сайта.
//
// Поля:
//   дата создания: дата и время, когда задача была создана;
//   статус: число (1 или 0), означающее, была ли выполнена задача. По умолчанию ноль;
//   название: задаётся пользователем;
//   файл: ссылка на файл, загруженный пользователем;
//   срок: дата, до которой задача должна быть выполнена.
// Связи:
//   автор: пользователь, создавший задачу;
//   проект: проект, которому принадлежит задача.
*/
CREATE TABLE tasks (
  id       	INT AUTO_INCREMENT PRIMARY KEY,
  dt_add   	DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status   	TINYINT NOT NULL DEFAULT 0,
  name     	CHAR(128) NOT NULL,
  file     	CHAR(255) DEFAULT NULL,
  dt_completion DATETIME DEFAULT NULL,
  user_id  	INT NOT NULL,
  project_id  	INT NOT NULL
);

CREATE INDEX idx_tasks_name ON tasks (name);
CREATE INDEX idx_tasks_dt_completion ON tasks (dt_completion);

/*
// = Пользователь =
// Представляет зарегистрированного пользователя.
//
// Поля:
//    дата регистрации: дата и время, когда этот пользователь завел аккаунт;
//    email;
//    имя;
//    пароль: хэшированный пароль пользователя.
*/
CREATE TABLE users (
  id       	INT AUTO_INCREMENT PRIMARY KEY,
  dt_add   	DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  email    	CHAR(128) NOT NULL UNIQUE,
  username 	CHAR(128) NOT NULL,
  password 	CHAR(64) NOT NULL
);

CREATE UNIQUE INDEX idx_users_email ON users (email);
CREATE INDEX idx_users_name ON users (username);
