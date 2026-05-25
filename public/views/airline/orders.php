<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $statusFilter */
/** @var string $userFilter */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Order;

$this->title = 'Заказы';

$statusBadges = [
    Order::STATUS_PENDING   => ['badge-wait',   'Ожидает оплаты'],
    Order::STATUS_PAID      => ['badge-paid',    'Оплачен'],
    Order::STATUS_CANCELLED => ['badge-cancel',  'Отменён'],
];

$statusOptions = [
    Order::STATUS_PENDING   => 'Ожидает оплаты',
    Order::STATUS_PAID      => 'Оплачен',
    Order::STATUS_CANCELLED => 'Отменён',
];
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Заказы</h4>
        <a href="<?= Url::to(['/airline/index']) ?>" class="btn btn-outline-secondary btn-sm">← Кабинет</a>
    </div>

    <?= \app\widgets\Alert::widget() ?>

    <form method="get" action="<?= Url::to(['/airline/orders']) ?>" class="mb-3 d-flex gap-2 align-items-center flex-wrap">
        <input type="text" name="user" class="form-control form-control-sm" style="max-width:220px"
               placeholder="Email или имя пассажира" value="<?= Html::encode($userFilter) ?>">
        <select name="order_status_id" class="form-select form-select-sm" style="max-width:180px">
            <option value="">Все статусы</option>
            <?php foreach ($statusOptions as $sid => $sname): ?>
            <option value="<?= $sid ?>" <?= $statusFilter == $sid ? 'selected' : '' ?>><?= Html::encode($sname) ?></option>
            <?php endforeach ?>
        </select>
        <button type="submit" class="btn btn-sm btn-primary">Найти</button>
        <?php if ($statusFilter || $userFilter): ?>
        <a href="<?= Url::to(['/airline/orders']) ?>" class="btn btn-sm btn-outline-secondary">↺ Сбросить</a>
        <?php endif ?>
    </form>

    <?php $orders = $dataProvider->getModels(); ?>
    <?php if (!$orders): ?>
        <div class="card p-5 text-center text-muted">
            <div style="font-size:2.5rem">📋</div>
            <p class="mt-2">Заказов на билеты вашей авиакомпании пока нет.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Пассажир</th>
                        <th>Маршрут</th>
                        <th>Рейс</th>
                        <th>Сумма</th>
                        <th>Оплата</th>
                        <th>Оформлен</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <?php
                        $firstItem = $order->orderItems[0] ?? null;
                        $flight = $firstItem ? $firstItem->ticket->flight : null;
                        [$badgeClass, $statusText] = $statusBadges[$order->order_status_id] ?? ['badge-wait', '?'];
                        $departed = $flight && strtotime($flight->departure_time) < time();
                    ?>
                    <tr>
                        <td class="fw-semibold">#<?= $order->id ?></td>
                        <td class="small">
                            <?= $order->user ? Html::encode($order->user->getFullName()) : '—' ?>
                            <?php if ($order->contact_email): ?>
                            <div class="text-muted"><?= Html::encode($order->contact_email) ?></div>
                            <?php endif ?>
                        </td>
                        <td>
                            <?php if ($flight): ?>
                                <?= Html::encode($flight->departureAirport->city) ?> → <?= Html::encode($flight->arrivalAirport->city) ?>
                            <?php endif ?>
                        </td>
                        <td class="small">
                            <?= $flight ? Html::encode($flight->flight_number) : '—' ?>
                            <?php if ($flight): ?>
                            <div class="text-muted"><?= date('d.m.Y H:i', strtotime($flight->departure_time)) ?></div>
                            <?php endif ?>
                        </td>
                        <td><?= $order->getFormattedTotal() ?></td>
                        <td class="small"><?= Html::encode($order->payType->title) ?></td>
                        <td class="small text-muted"><?= date('d.m.Y', strtotime($order->date ?? 'now')) ?></td>
                        <td><span class="<?= $badgeClass ?>"><?= $statusText ?></span></td>
                        <td>
                            <a href="<?= Url::to(['/airline/order-view', 'id' => $order->id]) ?>" class="btn btn-sm btn-outline-secondary">Детали</a>
                            <?php if ($order->order_status_id === Order::STATUS_PENDING && !$departed): ?>
                            <form method="post" action="<?= Url::to(['/airline/update-order-status', 'id' => $order->id]) ?>" style="display:inline"
                                  onsubmit="return confirm('Отметить заказ #<?= $order->id ?> как оплаченный?')">
                                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                                <input type="hidden" name="status_id" value="<?= Order::STATUS_PAID ?>">
                                <button type="submit" class="btn btn-sm btn-success">Оплачен</button>
                            </form>
                            <?php endif ?>
                            <?php if ($order->order_status_id !== Order::STATUS_CANCELLED && !$departed): ?>
                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal<?= $order->id ?>">Отменить</button>
                            <div class="modal fade" id="cancelModal<?= $order->id ?>" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title">Отмена заказа #<?= $order->id ?></h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="post" action="<?= Url::to(['/airline/update-order-status', 'id' => $order->id]) ?>">
                                            <div class="modal-body">
                                                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                                                <input type="hidden" name="status_id" value="<?= Order::STATUS_CANCELLED ?>">
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
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <?= \yii\bootstrap5\LinkPager::widget([
            'pagination' => $dataProvider->getPagination(),
            'options'    => ['class' => 'pagination justify-content-center mt-3'],
        ]) ?>
    <?php endif ?>
</div>
