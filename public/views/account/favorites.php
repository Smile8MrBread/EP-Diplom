<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $airlinesProvider */
/** @var yii\data\ActiveDataProvider $ticketsProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\LinkPager;

$this->title = 'Избранное';

$favAirlines = $airlinesProvider->getModels();
$favTickets  = $ticketsProvider->getModels();
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Избранное</h4>
        <a href="<?= Url::to(['/account/index']) ?>" class="btn btn-outline-secondary btn-sm">← Мои заказы</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h6 class="fw-bold mb-3">Авиакомпании (<?= $airlinesProvider->getTotalCount() ?>)</h6>
            <?php if ($favAirlines): ?>
                <?php foreach ($favAirlines as $fav): ?>
                <div class="card mb-2 p-3" id="fav-airline-<?= $fav->airline->id ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="<?= Url::to(['/catalog/airline', 'id' => $fav->airline->id]) ?>" class="fw-semibold text-decoration-none">
                                <?= Html::encode($fav->airline->name) ?>
                            </a>
                            <div class="text-muted small"><?= Html::encode($fav->airline->country) ?></div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="stars"><?= str_repeat('★', (int)round($fav->airline->averageRating)) ?></span>
                            <button class="btn btn-sm btn-outline-danger fav-remove-airline"
                                data-id="<?= $fav->airline->id ?>"
                                data-url="<?= Url::to(['/catalog/toggle-favorite-airline', 'id' => $fav->airline->id]) ?>"
                                title="Удалить из избранного">✕</button>
                        </div>
                    </div>
                </div>
                <?php endforeach ?>
                <?= LinkPager::widget([
                    'pagination' => $airlinesProvider->getPagination(),
                    'options'    => ['class' => 'pagination justify-content-start mt-2 mb-4'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions'          => ['class' => 'page-link'],
                ]) ?>
            <?php else: ?>
                <p class="text-muted small">Нет избранных авиакомпаний.</p>
            <?php endif ?>
        </div>

        <div class="col-md-6">
            <h6 class="fw-bold mb-3">Билеты (<?= $ticketsProvider->getTotalCount() ?>)</h6>
            <?php if ($favTickets): ?>
                <?php foreach ($favTickets as $fav): ?>
                <?php $ticket = $fav->ticket; $flight = $ticket->flight; ?>
                <div class="card mb-2 p-3" id="fav-ticket-<?= $ticket->id ?>">
                    <div class="route-line mb-1">
                        <span class="city"><?= Html::encode($flight->departureAirport->city) ?></span>
                        <span class="arrow">→</span>
                        <span class="city"><?= Html::encode($flight->arrivalAirport->city) ?></span>
                    </div>
                    <div class="text-muted small"><?= date('d.m.Y', strtotime($flight->departure_time)) ?> · <?= Html::encode($flight->airline->name) ?></div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="ticket-price" style="font-size:1.1rem"><?= $ticket->getFormattedPrice() ?></span>
                        <div class="d-flex gap-2">
                            <a href="<?= Url::to(['/catalog/ticket', 'id' => $ticket->id]) ?>" class="btn btn-primary btn-sm">Купить</a>
                            <button class="btn btn-sm btn-outline-danger fav-remove-ticket"
                                    data-id="<?= $ticket->id ?>"
                                    data-url="<?= Url::to(['/catalog/toggle-favorite-ticket', 'id' => $ticket->id]) ?>"
                                    title="Удалить из избранного">✕</button>
                        </div>
                    </div>
                </div>
                <?php endforeach ?>
                <?= LinkPager::widget([
                    'pagination' => $ticketsProvider->getPagination(),
                    'options'    => ['class' => 'pagination justify-content-start mt-2'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions'          => ['class' => 'page-link'],
                ]) ?>
            <?php else: ?>
                <p class="text-muted small">Нет избранных билетов.</p>
            <?php endif ?>
        </div>
    </div>
</div>
