<section class="content__side">
    <h2 class="content__side-heading">Проекты</h2>

    <nav class="main-navigation">
        <ul class="main-navigation__list">
            <?php foreach ($projects as $project): ?>
            <li class="main-navigation__list-item">
                <a class="main-navigation__list-item-link <?= mark_active_project($project['id']) ?>" href="<?= get_link_to_project($project['id']) ?>">
                    <?= strip_tags($project['name']) ?>
                </a>
                <span class="main-navigation__list-item-count"><?= number_project_tasks($tasks, $project['name']); ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <a class="button button--transparent button--plus content__side-button" href="pages/form-project.html" target="project_add">Добавить проект</a>
</section>

<main class="content__main">
    <h2 class="content__main-heading">Список задач</h2>

    <form class="search-form" action="index.php" method="post" autocomplete="off">
        <input class="search-form__input" type="text" name="" value="" placeholder="Поиск по задачам">

        <input class="search-form__submit" type="submit" name="" value="Искать">
    </form>

    <div class="tasks-controls">
        <nav class="tasks-switch">
            <a href="/" class="tasks-switch__item tasks-switch__item--active">Все задачи</a>
            <a href="/" class="tasks-switch__item">Повестка дня</a>
            <a href="/" class="tasks-switch__item">Завтра</a>
            <a href="/" class="tasks-switch__item">Просроченные</a>
        </nav>

        <label class="checkbox">
            <input class="checkbox__input visually-hidden show_completed" type="checkbox" <?= ($is_show_complete_tasks) ? "checked" : "" ?>>
            <span class="checkbox__text">Показывать выполненные</span>
        </label>
    </div>

    <table class="tasks">
        <?php foreach ($tasks as $task): ?>
            <?php $is_task_completed = (1 === $task['is_completed']); ?>
            <?php if (!$is_task_completed or $is_show_complete_tasks): ?>
        <tr class="tasks__item task <?= additional_task_classes($task, $is_show_complete_tasks) ?>">
            <td class="task__select">
                <label class="checkbox task__checkbox">
                    <input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="1" <?= ($is_task_completed) ? "checked" : "" ?>>
                    <span class="checkbox__text"><?= strip_tags($task['name']) ?></span>
                </label>
            </td>

            <!--td class="task__file">
                <a class="download-link" href="#">Home.psd</a>
            </td-->

            <td class="task__date"><?= strip_tags($task['date_completion']) ?></td>
        </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>
</main>
