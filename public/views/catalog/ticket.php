<?php

/** @var yii\web\View $this */
/** @var app\models\Ticket $ticket */
/** @var bool $isFavorite */

use yii\helpers\Html;
use yii\helpers\Url;

$flight = $ticket->flight;
$this->title = $flight->departureAirport->city . ' → ' . $flight->arrivalAirport->city . ' · ' . $ticket->cabinClass->class;
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= Url::to(['/catalog/index']) ?>">Каталог</a></li>
            <li class="breadcrumb-item"><a href="<?= Url::to(['/catalog/flight', 'id' => $flight->id]) ?>"><?= Html::encode($flight->departureAirport->city . ' → ' . $flight->arrivalAirport->city) ?></a></li>
            <li class="breadcrumb-item active"><?= Html::encode($ticket->cabinClass->class) ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <a href="<?= Url::to(['/catalog/airline', 'id' => $flight->airline->id]) ?>"
                           class="fw-bold text-decoration-none" style="color:var(--ab-blue)">
                            <?= Html::encode($flight->airline->name) ?>
                        </a>
                        <span class="text-muted ms-2 small"><?= Html::encode($flight->flight_number) ?></span>
                    </div>
                    <?php if (!Yii::$app->user->isGuest): ?>
                    <button class="fav-btn <?= $isFavorite ? 'active' : '' ?>" id="fav-ticket"
                            data-url="<?= Url::to(['/catalog/toggle-favorite-ticket', 'id' => $ticket->id]) ?>"
                            title="В избранное">♥</button>
                    <?php endif ?>
                </div>

                <!-- Route -->
                <div class="row text-center mb-4">
                    <div class="col">
                        <div class="fw-bold" style="font-size:1.5rem"><?= Html::encode($flight->departureAirport->city) ?></div>
                        <div class="text-muted small"><?= Html::encode($flight->departureAirport->name) ?></div>
                        <div class="fw-semibold mt-1"><?= date('d.m.Y', strtotime($flight->departure_time)) ?></div>
                        <div style="font-size:1.2rem;color:var(--ab-blue)"><?= date('H:i', strtotime($flight->departure_time)) ?></div>
                    </div>
                    <div class="col-auto align-self-center text-muted">
                        <div style="font-size:1.4rem">→</div>
                        <div class="small"><?= $flight->durationFormatted ?></div>
                    </div>
                    <div class="col">
                        <div class="fw-bold" style="font-size:1.5rem"><?= Html::encode($flight->arrivalAirport->city) ?></div>
                        <div class="text-muted small"><?= Html::encode($flight->arrivalAirport->name) ?></div>
                        <div class="fw-semibold mt-1"><?= date('d.m.Y', strtotime($flight->arrival_time)) ?></div>
                        <div style="font-size:1.2rem;color:var(--ab-blue)"><?= date('H:i', strtotime($flight->arrival_time)) ?></div>
                    </div>
                </div>

                <!-- Details -->
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="text-muted small">Класс</div>
                        <div class="fw-semibold"><?= Html::encode($ticket->cabinClass->class) ?></div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Тариф</div>
                        <div class="fw-semibold"><?= Html::encode($ticket->fare_type) ?></div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Багаж</div>
                        <div class="fw-semibold"><?= Html::encode($ticket->baggage_info) ?></div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Мест</div>
                        <div class="fw-semibold"><?= $ticket->available_quantity ?></div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Статус рейса</div>
                        <div class="fw-semibold"><?= Html::encode($flight->flightStatus->status) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Price & Buy -->
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <div class="text-muted small mb-1">Стоимость за 1 билет</div>
                <div class="ticket-price mb-3"><?= $ticket->getFormattedPrice() ?></div>

                <?php if (Yii::$app->user->isGuest): ?>
                    <a href="<?= Url::to(['/site/login']) ?>" class="btn btn-primary w-100">
                        Войдите для покупки
                    </a>
                <?php elseif (Yii::$app->user->identity?->isUser && $ticket->available_quantity > 0): ?>
                    <a href="<?= Url::to(['/account/buy', 'ticketId' => $ticket->id]) ?>" class="btn btn-primary w-100 py-2 fw-semibold">
                        Оформить заказ
                    </a>
                <?php else: ?>
                    <button class="btn btn-secondary w-100" disabled>Недоступно</button>
                <?php endif ?>

                <p class="text-muted small mt-3 mb-0">
                    Бесплатная отмена в течение 24 часов после оплаты
                </p>
            </div>
        </div>
    </div>
</div>

