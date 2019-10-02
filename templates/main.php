<section class="content__side">
    <h2 class="content__side-heading">Проекты</h2>

    <nav class="main-navigation">
        <ul class="main-navigation__list">
            <?php foreach ($projects as $project) : ?>
            <li class="main-navigation__list-item">
                <a class="main-navigation__list-item-link <?= mark_active_project($project['id']) ?>" href="/index.php<?= set_project_query($project['id']); ?>">
                    <?= strip_tags($project['name']) ?>
                </a>
                <span class="main-navigation__list-item-count"><?= $project['cnt_tasks']; ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <a class="button button--transparent button--plus content__side-button" href="/add_project.php">Добавить проект</a>
</section>

<main class="content__main">
    <h2 class="content__main-heading">Список задач</h2>

    <form class="search-form" action="index.php" method="get" autocomplete="off">
        <input class="search-form__input" type="text" name="search" value="<?= $search['text']; ?>" placeholder="Поиск по задачам">

        <input class="search-form__submit" type="submit" name="" value="Искать">
    </form>

    <div class="tasks-controls">
        <nav class="tasks-switch">
            <a href="/index.php<?= get_all_tasks_query_filter(); ?>"
                class="tasks-switch__item <?= mark_active_no_filter_tasks(); ?>"
            >Все задачи
            </a>
            <a href="/index.php<?= get_tasks_filter_query_for_today(); ?>"
                class="tasks-switch__item <?= mark_active_exist_filter_tasks(TASKS_FILTER_TODAY); ?>"
            >Повестка дня
            </a>
            <a href="/index.php<?= get_tasks_filter_query_for_tomorrow(); ?>"
                class="tasks-switch__item <?= mark_active_exist_filter_tasks(TASKS_FILTER_TOMORROW); ?>"
            >Завтра
            </a>
            <a href="/index.php<?= get_tasks_filter_query_for_expired(); ?>"
                class="tasks-switch__item <?= mark_active_exist_filter_tasks(TASKS_FILTER_EXPIRED); ?>"
            >Просроченные
            </a>
        </nav>

        <label class="checkbox">
            <input class="checkbox__input visually-hidden show_completed" type="checkbox" <?= ($is_show_complete_tasks) ? "checked" : "" ?>>
            <span class="checkbox__text">Показывать выполненные</span>
        </label>
    </div>

    <table class="tasks">
        <?php foreach ($tasks as $task) : ?>
            <?php $is_task_completed = (1 === $task['is_completed']); ?>
            <?php if (!$is_task_completed or $is_show_complete_tasks) : ?>
        <tr class="tasks__item task <?= additional_task_classes($task, $is_show_complete_tasks) ?>">
            <td class="task__select">
                <label class="checkbox task__checkbox">
                    <input class="checkbox__input visually-hidden task__checkbox" type="checkbox"
                        value="<?= $task['id'] ?>" <?= ($is_task_completed) ? "checked" : "" ?>>
                    <span class="checkbox__text"><?= strip_tags($task['name']) ?></span>
                </label>
            </td>

            <td class="task__file">
                <?php if (!is_null($task['file'])) : ?>
                <a class="download-link" href="<?= '/uploads/' . $task['file']; ?>" target="_blank"><?= strip_tags($task['file']) ?></a>
                <?php endif; ?>
            </td>

            <td class="task__date"><?= euro_date(strip_tags($task['date_completion'])); ?></td>
        </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>

    <?php if ($search['is_search'] && empty($task)) : ?>
    <p>Ничего не найдено по вашему запросу</p>
    <?php endif; ?>
</main>
