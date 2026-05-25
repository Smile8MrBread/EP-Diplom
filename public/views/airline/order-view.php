<?php

/** @var yii\web\View $this */
/** @var app\models\Order $order */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Order;

$this->title = 'Заказ #' . $order->id;

$badges = [
    Order::STATUS_PENDING   => 'badge-wait',
    Order::STATUS_PAID      => 'badge-paid',
    Order::STATUS_CANCELLED => 'badge-cancel',
];
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= Url::to(['/airline/index']) ?>">Кабинет</a></li>
            <li class="breadcrumb-item"><a href="<?= Url::to(['/airline/orders']) ?>">Заказы</a></li>
            <li class="breadcrumb-item active">Заказ #<?= $order->id ?></li>
        </ol>
    </nav>

    <?= \app\widgets\Alert::widget() ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card p-4 mb-3">
                <h5 class="fw-bold mb-3">Состав заказа</h5>
                <?php foreach ($order->orderItems as $item): ?>
                <?php $ticket = $item->ticket; $flight = $ticket->flight; ?>
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold"><?= Html::encode($flight->flight_number) ?></span>
                        <span class="badge bg-light text-secondary border"><?= Html::encode($ticket->cabinClass->class) ?></span>
                    </div>
                    <div class="route-line mb-2">
                        <span class="city"><?= Html::encode($flight->departureAirport->city) ?></span>
                        <span class="arrow">→</span>
                        <span class="city"><?= Html::encode($flight->arrivalAirport->city) ?></span>
                        <span class="dur"><?= $flight->durationFormatted ?></span>
                    </div>
                    <div class="row text-muted small">
                        <div class="col">Вылет: <?= date('d.m.Y H:i', strtotime($flight->departure_time)) ?></div>
                        <div class="col">Прилёт: <?= date('d.m.Y H:i', strtotime($flight->arrival_time)) ?></div>
                    </div>
                    <div class="row mt-2 small">
                        <div class="col">Кол-во: <?= $item->quantity ?></div>
                        <div class="col">Цена за 1: <?= number_format($item->unit_price, 0, '.', ' ') ?> <?= $order->currency_code ?></div>
                        <div class="col fw-semibold">Итого: <?= number_format($item->line_total, 0, '.', ' ') ?> <?= $order->currency_code ?></div>
                    </div>
                </div>
                <?php endforeach ?>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 mb-3">
                <h6 class="fw-bold mb-3">Информация о заказе</h6>
                <dl class="small mb-0">
                    <dt class="text-muted">Номер заказа</dt><dd class="fw-semibold">#<?= $order->id ?></dd>
                    <dt class="text-muted">Статус</dt>
                    <dd><span class="<?= $badges[$order->order_status_id] ?? 'badge-wait' ?>"><?= Html::encode($order->orderStatus->status) ?></span></dd>
                    <dt class="text-muted">Способ оплаты</dt><dd><?= Html::encode($order->payType->title) ?></dd>
                    <dt class="text-muted">Дата заказа</dt><dd><?= date('d.m.Y H:i', strtotime($order->date ?? 'now')) ?></dd>
                </dl>
                <hr>
                <div class="d-flex justify-content-between fw-bold small">
                    <span>Итого</span>
                    <span><?= $order->getFormattedTotal() ?></span>
                </div>
            </div>

            <div class="card p-4 mb-3">
                <h6 class="fw-bold mb-3">Пассажир</h6>
                <dl class="small mb-0">
                    <?php if ($order->user): ?>
                    <dt class="text-muted">ФИО</dt><dd><?= Html::encode($order->user->getFullName()) ?></dd>
                    <?php endif ?>
                    <dt class="text-muted">Email</dt><dd><?= Html::encode($order->contact_email) ?></dd>
                    <dt class="text-muted">Телефон</dt><dd><?= Html::encode($order->contact_phone) ?></dd>
                    <?php if ($order->order_status_id === Order::STATUS_CANCELLED && $order->comment_text): ?>
                    <dt class="text-muted">Причина отмены</dt>
                    <dd class="text-danger"><?= Html::encode($order->comment_text) ?></dd>
                    <?php endif ?>
                </dl>
            </div>

            <?php
            $viewDeparted = false;
            foreach ($order->orderItems as $oi) {
                $of = $oi->ticket->flight ?? null;
                if ($of && strtotime($of->departure_time) < time()) { $viewDeparted = true; break; }
            }
            ?>
            <?php if ($order->order_status_id === Order::STATUS_PENDING && !$viewDeparted): ?>
            <div class="card p-4 mb-3">
                <h6 class="fw-bold mb-3">Подтвердить оплату</h6>
                <form method="post" action="<?= Url::to(['/airline/update-order-status', 'id' => $order->id]) ?>"
                      onsubmit="return confirm('Отметить заказ #<?= $order->id ?> как оплаченный?')">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                    <input type="hidden" name="ref" value="view">
                    <input type="hidden" name="status_id" value="<?= Order::STATUS_PAID ?>">
                    <button type="submit" class="btn btn-success btn-sm w-100">Оплачен</button>
                </form>
            </div>
            <?php endif ?>
            <?php if ($order->order_status_id !== Order::STATUS_CANCELLED && !$viewDeparted): ?>
            <div class="card p-4 mb-3">
                <h6 class="fw-bold mb-3">Отменить заказ</h6>
                <form method="post" action="<?= Url::to(['/airline/update-order-status', 'id' => $order->id]) ?>">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                    <input type="hidden" name="ref" value="view">
                    <input type="hidden" name="status_id" value="<?= Order::STATUS_CANCELLED ?>">
                    <label class="form-label small fw-semibold">Причина отмены <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control form-control-sm mb-2" rows="2" placeholder="Укажите причину" required></textarea>
                    <button type="submit" class="btn btn-danger btn-sm w-100">Отменить заказ</button>
                </form>
            </div>
            <?php endif ?>
        </div>
    </div>
</div>
