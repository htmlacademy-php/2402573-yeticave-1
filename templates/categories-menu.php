<?php if (!empty($promo)): ?>
    <section class="promo">
        <div class="container">
            <h2 class="promo__title">Нужен стафф для катки?</h2>
            <p class="promo__text">
                На нашем интернет-аукционе ты найдёшь самое эксклюзивное
                сноубордическое и горнолыжное снаряжение.
            </p>
            <ul class="promo__list">
                <?php foreach ($categories as $item): ?>
                    <li class="promo__item promo__item--<?= htmlspecialchars($item['symbol_code']) ?>">
                        <a class="promo__link" href="all-lots.php?id=<?= htmlspecialchars($item['id']) ?>">
                            <?= htmlspecialchars($item['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php else: ?>
    <nav class="nav">
        <ul class="nav__list container">
            <?php foreach ($categories as $cat): ?>
                <li class="nav__item <?= (isset($_GET['id']) && $_GET['id'] == $cat['id']) ? 'nav__item--current' : '' ?>">
                    <a href="all-lots.php?id=<?= htmlspecialchars($cat['id']) ?>">
                        <?= htmlspecialchars($cat['title']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
<?php endif; ?>
