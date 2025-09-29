<section class="promo">
    <h2 class="promo__title">Нужен стафф для катки?</h2>
    <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное снаряжение.</p>
    <ul class="promo__list">
        <?php foreach ($categories as $item): ?>
            <li class="promo__item promo__item--<?= htmlspecialchars($item['symbol_code']) ?>">
                <a class="promo__link" href="pages/all-lots.html"><?= htmlspecialchars($item['title']) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<section class="lots">
    <div class="lots__header">
        <h2>Открытые лоты</h2>
    </div>
    <ul class="lots__list">
        <?php foreach ($lots as $lot): ?>
            <li class="lots__item lot">
                <div class="lot__image">
                    <a href="lot.php?id=<?= $lot['id'] ?>"><img src="<?= htmlspecialchars($lot['image']) ?>" width="350" height="260" alt="<?= htmlspecialchars($lot['lot_title']) ?>"></a>
                </div>
                <div class="lot__info">
                    <span class="lot__category"><?= htmlspecialchars($lot['category_title']) ?></span>
                    <h3 class="lot__title"><a class="text-link" href="lot.php?id=<?= $lot['id'] ?>"><?= htmlspecialchars($lot['lot_title']) ?></a></h3>
                    <div class="lot__state">
                        <div class="lot__rate">
                            <span class="lot__amount">Стартовая цена</span>
                            <span class="lot__cost"><?= formatThePrice($lot['starting_price']) ?></span>
                        </div>
                        <?php $time = getDtRange($lot['end_date']); ?>
                        <div class="lot__timer timer<?php if ($time[0] < 1): ?> timer--finishing<?php endif; ?>">
                            <?= str_pad($time[0], 2, '0', STR_PAD_LEFT) . ': ' . str_pad($time[1], 2, '0', STR_PAD_LEFT) ?>
                        </div>
                    </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
