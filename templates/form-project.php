      <section class="content__side">
        <h2 class="content__side-heading">Проекты</h2>

        <nav class="main-navigation">
            <ul class="main-navigation__list">
            <?php foreach ($projects as $project): ?>
                <li class="main-navigation__list-item">
                    <a class="main-navigation__list-item-link <?= mark_active_project($project['id']) ?>" href="<?= get_link_to_project($project['id']) ?>">
                        <?= strip_tags($project['name']) ?>
                    </a>
                    <span class="main-navigation__list-item-count"><?= $project['cnt_tasks']; ?></span>
                </li>
            <?php endforeach; ?>
            </ul>
        </nav>

        <a class="button button--transparent button--plus content__side-button" href="add_project.php">Добавить проект</a>
      </section>

      <main class="content__main">
        <h2 class="content__main-heading">Добавление проекта</h2>

        <form class="form" action="add_project.php" method="post" autocomplete="off">
          <div class="form__row">
            <label class="form__label" for="project_name">Название <sup>*</sup></label>

            <input class="form__input <?= isset($errors['name']) ? 'form__input--error' : '' ?>" type="text" name="name" id="project_name"
                value="<?= ($new_project['name'] ?? ''); ?>" placeholder="Введите название проекта">

            <?php if (isset($errors['name'])): ?>
                <p class="form__message"><?= $errors['name']; ?></p>
            <?php endif; ?>
          </div>

          <div class="form__row form__row--controls">
            <input class="button" type="submit" name="" value="Добавить">
          </div>
        </form>
      </main>
