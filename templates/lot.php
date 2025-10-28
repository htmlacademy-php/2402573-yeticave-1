<?php
$classInvalid = !empty($errors) ? ' form--invalid' : '';
?>

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
                        <span class="lot-item__cost"><?= formatThePrice($currentPrice) ?></span>
                    </div>
                    <div class="lot-item__min-cost">
                        Мин. ставка <span><?= $minBid ?> р</span>
                    </div>
 </div>
            </div>
                    <?php if ($isFormVisible): ?>
                        <?php if (isset($_SESSION['user'])): ?>
                            <?php $classForm = !empty($errors) ? ' form--invalid' : ''; ?>
                            <form class="lot-item__form<?= $classForm ?>" action="add-bet.php" method="post" autocomplete="off">
                                <input type="hidden" name="lot_id" value="<?= htmlspecialchars($lot['id']) ?>">
                                <?php $classInput = isset($errors['cost']) ? ' form__item--invalid' : ''; ?>
                                <p class="lot-item__form-item form__item<?= $classInput ?>">
                                    <label for="cost">Ваша ставка</label>
                                    <input id="cost" type="number" name="cost"
                                        placeholder="<?= htmlspecialchars($currentPrice) ?>"
                                        value="<?= htmlspecialchars($costValue ?? $_POST['cost'] ?? '') ?>">
                                    <span class="form__error"><?= $errors['cost'] ?? '' ?></span>
                                </p>
                                <button type="submit" class="button">Сделать ставку</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

            <div class="history">
                <?php $bidsHistory = $bidsHistory ?? []; ?>
                <h3>История ставок (<span><?= count($bidsHistory) ?></span>)</h3>
                <table class="history__list">
                    <?php foreach ($bidsHistory as $bid): ?>
                        <tr class="history__item">
                            <td class="history__name"><?= htmlspecialchars($bid['user_name']) ?></td>
                            <td class="history__price"><?= formatThePrice($bid['amount']) ?></td>
                            <td class="history__time"><?= htmlspecialchars(countTimePosted($bid['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</section>
