<nav class="nav">
    <ul class="nav__list container">
        <?php foreach ($categories as $item): ?>
            <li class="nav__item <?php if ($item['id'] == $category['id']) echo 'nav__item--current'; ?>">
                <a href="all-lots.php?id=<?= htmlspecialchars($item['id']) ?>">
                    <?= htmlspecialchars($item['title']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<div class="container">
    <section class="lots">
        <h2>Все лоты в категории <span>«<?= htmlspecialchars($category['title']) ?>»</span></h2>

        <ul class="lots__list">
            <?php foreach ($lots as $lot): ?>
                <li class="lots__item lot">
                    <div class="lot__image">
                        <a href="lot.php?id=<?= $lot['id'] ?>">
                            <img src="<?= htmlspecialchars($lot['image']) ?>" width="350" height="260" alt="<?= htmlspecialchars($lot['lot_title']) ?>">
                        </a>
                    </div>
                    <div class="lot__info">
                        <span class="lot__category"><?= htmlspecialchars($lot['category_title']) ?></span>
                        <h3 class="lot__title">
                            <a class="text-link" href="lot.php?id=<?= $lot['id'] ?>"><?= htmlspecialchars($lot['lot_title']) ?></a>
                        </h3>
                        <div class="lot__state">
                            <div class="lot__rate">
                                <span class="lot__amount">
                                    <?= $lot['bets_count'] > 0 ? $lot['bets_count'] . ' ставка' : 'Стартовая цена' ?>
                                </span>
                                <span class="lot__cost"><?= formatThePrice($lot['starting_price']) ?></span>
                            </div>
                            <?php $time = getDtRange($lot['end_date'] ?? date('Y-m-d H:i:s')); ?>
                            <div class="lot__timer timer<?php if ($time[0] < 1) echo ' timer--finishing'; ?>">
                                <?= str_pad($time[0], 2, '0', STR_PAD_LEFT) . ':' . str_pad($time[1], 2, '0', STR_PAD_LEFT) ?>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <?php if ($pagesCount > 1): ?>
        <ul class="pagination-list">
            <?php for ($i = 1; $i <= $pagesCount; $i++): ?>
                <li class="pagination-item <?php if ($i === $currentPage) echo 'pagination-item--active'; ?>">
                    <a href="?id=<?= $category['id'] ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    <?php endif; ?>
</div>
