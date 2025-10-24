<h1>Результаты поиска по запросу «<?= htmlspecialchars($search) ?>»</h1>

<?php if (empty($lots)): ?>
    <p>Ничего не найдено по вашему запросу.</p>
<?php else: ?>
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
                        <a href="lot.php?id=<?= $lot['id'] ?>"><?= htmlspecialchars($lot['lot_title']) ?></a>
                    </h3>
                    <div class="lot__state">
                        <div class="lot__rate">
                            <span class="lot__amount">Начальная цена</span>
                            <span class="lot__cost"><?= formatThePrice($lot['starting_price']) ?></span>
                        </div>
                        <div class="lot__timer timer">
                            <?php $time = getDtRange($lot['end_date']); ?>
                            <?= str_pad($time[0], 2, '0', STR_PAD_LEFT) . ':' . str_pad($time[1], 2, '0', STR_PAD_LEFT) ?>
                        </div>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($totalPages > 1): ?>
        <ul class="pagination-list">
            <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                <li class="pagination-item <?= $page === $currentPage ? 'pagination-item-active' : '' ?>">
                    <a href="search.php?search=<?= urlencode($search) ?>&page=<?= $page ?>">
                        <?= $page ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

