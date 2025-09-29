<section class="lot-item container">
    <a href="lot.php?id=<?= $lot['id'] ?>">
        <h2><?= htmlspecialchars($lot['lot_title']) ?></h2>
    </a>
    <div class="lot-item__content">
        <div class="lot-item__left">
            <div class="lot-item__image">
                <a href="lot.php?id=<?= $lot['id'] ?>"><img src="<?= htmlspecialchars($lot['image']) ?>" width="730" height="548" alt="<?= htmlspecialchars($lot['title']) ?>"></a>
            </div>
            <p class="lot-item__category">Категория: <span><?= htmlspecialchars($lot['category_title']) ?></span></p>
            <p class="lot-item__description"><?= htmlspecialchars($lot['description']) ?></p>
        </div>
        <div class="lot-item__right">
            <div class="lot-item__state">
                <?php $time = getDtRange($lot['end_date']); ?>
                <div class="lot__timer timer<?php if ($time[0] < 1): ?> timer--finishing<?php endif; ?>">
                    <?= str_pad($time[0], 2, '0', STR_PAD_LEFT) . ': ' . str_pad($time[1], 2, '0', STR_PAD_LEFT) ?>
                </div>
                <div class="lot-item__cost-state">
                    <div class="lot-item__rate">
                        <span class="lot-item__amount">Текущая цена</span>
                        <span class="lot-item__cost"><?= formatThePrice(htmlspecialchars($lot['starting_price'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
