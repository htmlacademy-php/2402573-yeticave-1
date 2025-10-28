<section class="rates container">
    <h2>Мои ставки</h2>
    <table class="rates__list">
        <?php foreach ($bids as $bid) : ?>
            <tr class="rates__item">
                <td class="rates__info">
                    <div class="rates__img">
                        <img src="<?= htmlspecialchars($bid['lot_image']) ?>" width="54" height="40" alt="<?= htmlspecialchars($bid['lot_title']) ?>">
                    </div>
                    <div>
                        <h3 class="rates__title">
                            <a href="lot.php?id=<?= htmlspecialchars($bid['lot_id']) ?>"><?= htmlspecialchars($bid['lot_title']) ?></a>
                        </h3>
                        <?php if ($bid['is_winner'] && !empty($bid['contact_info'])) : ?>
                            <p><?= htmlspecialchars($bid['contact_info']) ?></p>
                        <?php endif; ?>
                    </div>
                </td>
                <td class="rates__category">
                    <?= htmlspecialchars($bid['category_title']) ?>
                </td>
                <td class="rates__timer">
                    <?php if ($bid['is_winner']) : ?>
                        <div class="timer timer--win">Ставка выиграла</div>
                    <?php elseif ($bid['expired']) : ?>
                        <div class="timer timer--end">Торги окончены</div>
                    <?php else : ?>
                        <div class="timer <?php if ($bid['time'][0] < 1) {
                            echo 'timer--finishing';
                                          } ?>">
                            <?= str_pad($bid['time'][0], 2, '0', STR_PAD_LEFT) . ':' . str_pad($bid['time'][1], 2, '0', STR_PAD_LEFT) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="rates__price">
                    <?= htmlspecialchars(formatThePrice($bid['amount'])) ?>
                </td>
                <td class="rates__time">
                    <?= htmlspecialchars(countTimePosted($bid['created_at'])) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
