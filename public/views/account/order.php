<?php

/** @var yii\web\View $this */
/** @var app\models\Order $order */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Order;

$this->title = 'Заказ #' . $order->id;
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= Url::to(['/account/index']) ?>">Мои заказы</a></li>
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
                        <span class="fw-semibold"><?= Html::encode($flight->airline->name) ?> · <?= Html::encode($flight->flight_number) ?></span>
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
            <div class="card p-4">
                <h6 class="fw-bold mb-3">Информация о заказе</h6>
                <dl class="small mb-0">
                    <dt class="text-muted">Номер заказа</dt><dd class="fw-semibold">#<?= $order->id ?></dd>
                    <dt class="text-muted">Статус</dt>
                    <dd>
                        <?php
                        $badges = [Order::STATUS_PENDING=>'badge-wait', Order::STATUS_PAID=>'badge-paid', Order::STATUS_CANCELLED=>'badge-cancel'];
                        ?>
                        <span class="<?= $badges[$order->order_status_id] ?? 'badge-wait' ?>"><?= Html::encode($order->orderStatus->status) ?></span>
                    </dd>
                    <dt class="text-muted">Способ оплаты</dt><dd><?= Html::encode($order->payType->title) ?></dd>
                    <dt class="text-muted">Email</dt><dd><?= Html::encode($order->contact_email) ?></dd>
                    <dt class="text-muted">Телефон</dt><dd><?= Html::encode($order->contact_phone) ?></dd>
                    <?php if ($order->order_status_id === Order::STATUS_CANCELLED && $order->comment_text): ?>
                    <dt class="text-muted">Причина отмены</dt>
                    <dd class="text-danger"><?= Html::encode($order->comment_text) ?></dd>
                    <?php endif ?>
                </dl>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Итого</span>
                    <span><?= $order->getFormattedTotal() ?></span>
                </div>
                <?php
                $flightDeparted = false;
                foreach ($order->orderItems as $item) {
                    if ($item->ticket->flight->departure_time && strtotime($item->ticket->flight->departure_time) < time()) {
                        $flightDeparted = true;
                        break;
                    }
                }
                ?>
                <?php if ($order->order_status_id === Order::STATUS_PENDING && !$flightDeparted): ?>
                <button class="btn btn-outline-danger btn-sm w-100 mt-3" data-bs-toggle="modal" data-bs-target="#cancelModal">Отменить заказ</button>
                <?php endif ?>
                <a href="<?= Url::to(['/account/index']) ?>" class="btn btn-outline-secondary btn-sm w-100 mt-2">← К заказам</a>
            </div>
        </div>
    </div>
</div>

<?php if ($order->order_status_id === Order::STATUS_PENDING && !$flightDeparted): ?>
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Отмена заказа #<?= $order->id ?></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= Url::to(['/account/cancel', 'id' => $order->id]) ?>">
                <div class="modal-body">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                    <label class="form-label small fw-semibold">Причина отмены <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control form-control-sm" rows="2" placeholder="Укажите причину" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Назад</button>
                    <button type="submit" class="btn btn-sm btn-danger">Отменить заказ</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif ?>
