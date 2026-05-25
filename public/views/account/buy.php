<?php

/** @var yii\web\View $this */
/** @var app\models\Ticket $ticket */
/** @var array $payTypes */

use yii\helpers\Html;
use yii\helpers\Url;

$flight = $ticket->flight;
$user   = Yii::$app->user->identity;
$this->title = 'Оформление заказа';
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= Url::to(['/catalog/index']) ?>">Каталог</a></li>
            <li class="breadcrumb-item"><a href="<?= Url::to(['/catalog/ticket', 'id' => $ticket->id]) ?>">Билет</a></li>
            <li class="breadcrumb-item active">Оформление</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Flight summary -->
        <div class="col-md-5 mb-4">
            <div class="card p-4">
                <h6 class="fw-bold mb-3">Ваш рейс</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold"><?= Html::encode($flight->airline->name) ?></span>
                    <span class="text-muted small"><?= Html::encode($flight->flight_number) ?></span>
                </div>
                <div class="route-line mb-2">
                    <span class="city"><?= Html::encode($flight->departureAirport->city) ?></span>
                    <span class="arrow">→</span>
                    <span class="city"><?= Html::encode($flight->arrivalAirport->city) ?></span>
                </div>
                <div class="text-muted small mb-3">
                    <?= date('d.m.Y H:i', strtotime($flight->departure_time)) ?> — <?= date('H:i', strtotime($flight->arrival_time)) ?>
                    · <?= $flight->durationFormatted ?>
                </div>
                <div class="row small text-muted">
                    <div class="col">Класс: <strong><?= Html::encode($ticket->cabinClass->class) ?></strong></div>
                    <div class="col">Багаж: <strong><?= Html::encode($ticket->baggage_info) ?></strong></div>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Цена за билет</span>
                    <span class="ticket-price"><?= $ticket->getFormattedPrice() ?></span>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="col-md-7">
            <div class="card p-4">
                <h6 class="fw-bold mb-3">Данные для заказа</h6>
                <form method="post">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">

                    <div class="mb-3">
                        <label class="form-label">Количество мест</label>
                        <input type="number" name="quantity" class="form-control" value="1"
                               min="1" max="<?= $ticket->available_quantity ?>" required>
                        <div class="form-text">Доступно: <?= $ticket->available_quantity ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Контактный email</label>
                        <input type="email" name="contact_email" class="form-control"
                               value="<?= Html::encode($user->email) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Контактный телефон</label>
                        <input type="tel" name="contact_phone" class="form-control"
                               value="<?= Html::encode($user->phone) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Способ оплаты</label>
                        <select name="pay_type_id" class="form-select" required>
                            <option value="">Выберите способ</option>
                            <?php foreach ($payTypes as $id => $title): ?>
                                <option value="<?= $id ?>"><?= Html::encode($title) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Комментарий <span class="text-muted">(необязательно)</span></label>
                        <textarea name="comment_text" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold">Подтвердить заказ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
