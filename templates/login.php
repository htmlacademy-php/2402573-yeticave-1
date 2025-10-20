    <?php
    $classInvalid = !empty($errors) ? ' form--invalid' : '';
    ?>

    <form class="form container <?= $classInvalid ?>" action="login.php" method="post"> <!-- form--invalid -->
      <h2>Вход</h2>
       <div class="form__item<?= isset($errors['email']) ? ' form__item--invalid' : '' ?>">
            <label for="email">E-mail <sup>*</sup></label>
            <input id="email" type="text" name="email" value="<?= htmlspecialchars($form['email'] ?? '') ?>" placeholder="Введите e-mail">
            <span class="form__error">
                <?= $errors['email'] ?? '' ?>
            </span>
        </div>
      <div class="form__item<?= isset($errors['password']) ? ' form__item--invalid' : '' ?>">
            <label for="password">Пароль <sup>*</sup></label>
            <input id="password" type="password" name="password" placeholder="Введите пароль">
            <span class="form__error"><span class="form__error"><?= $errors['password'] ?? '' ?></span></span>
        </div>
        <?php if (!empty($errors)): ?>
            <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
        <?php endif; ?>
      <button type="submit" class="button">Войти</button>
    </form>
