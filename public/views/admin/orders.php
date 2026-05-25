<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $statusFilter */
/** @var string $userFilter */
/** @var int $airlineFilter */
/** @var app\models\Airline[] $airlines */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Order;

$this->title = 'Все заказы';

$statusBadges = [
    Order::STATUS_PENDING   => 'badge-wait',
    Order::STATUS_PAID      => 'badge-paid',
    Order::STATUS_CANCELLED => 'badge-cancel',
];

$statusOptions = [
    Order::STATUS_PENDING   => 'Ожидает оплаты',
    Order::STATUS_PAID      => 'Оплачен',
    Order::STATUS_CANCELLED => 'Отменён',
];
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Все заказы</h4>
        <a href="<?= Url::to(['/admin/index']) ?>" class="btn btn-outline-secondary btn-sm">← Панель</a>
    </div>

    <form method="get" action="<?= Url::to(['/admin/orders']) ?>" class="mb-3 d-flex gap-2 align-items-center flex-wrap">
        <input type="text" name="user" class="form-control form-control-sm" style="max-width:200px"
               placeholder="Email или имя пассажира" value="<?= Html::encode($userFilter) ?>">
        <select name="airline_id" class="form-select form-select-sm" style="max-width:200px">
            <option value="">Все авиакомпании</option>
            <?php foreach ($airlines as $a): ?>
            <option value="<?= $a->id ?>" <?= $airlineFilter == $a->id ? 'selected' : '' ?>><?= Html::encode($a->name) ?></option>
            <?php endforeach ?>
        </select>
        <select name="order_status_id" class="form-select form-select-sm" style="max-width:170px">
            <option value="">Все статусы</option>
            <?php foreach ($statusOptions as $sid => $sname): ?>
            <option value="<?= $sid ?>" <?= $statusFilter == $sid ? 'selected' : '' ?>><?= Html::encode($sname) ?></option>
            <?php endforeach ?>
        </select>
        <button type="submit" class="btn btn-sm btn-primary">Найти</button>
        <?php if ($statusFilter || $userFilter || $airlineFilter): ?>
        <a href="<?= Url::to(['/admin/orders']) ?>" class="btn btn-sm btn-outline-secondary">↺ Сбросить</a>
        <?php endif ?>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Пользователь</th>
                    <th>Авиакомпания</th>
                    <th>Маршрут</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Оформлен</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $order): ?>
                <?php
                $firstItem = $order->orderItems[0] ?? null;
                $firstFlight = $firstItem->ticket->flight ?? null;
                $departed = $firstFlight && strtotime($firstFlight->departure_time) < time();
                ?>
                <tr>
                    <td class="fw-semibold">#<?= $order->id ?></td>
                    <td class="small"><?= $order->user ? Html::encode($order->user->getFullName()) : '—' ?></td>
                    <td class="small">
                        <?php if ($firstItem): ?>
                            <?= Html::encode($firstItem->ticket->flight->airline->name) ?>
                        <?php endif ?>
                    </td>
                    <td>
                        <?php if ($firstItem): ?>
                            <?php $flight = $firstItem->ticket->flight; ?>
                            <?= Html::encode($flight->departureAirport->city) ?> → <?= Html::encode($flight->arrivalAirport->city) ?>
                        <?php endif ?>
                    </td>
                    <td><?= $order->getFormattedTotal() ?></td>
                    <td><span class="<?= $statusBadges[$order->order_status_id] ?? 'badge-wait' ?>"><?= Html::encode($order->orderStatus->status) ?></span></td>
                    <td class="small text-muted"><?= date('d.m.Y', strtotime($order->date ?? 'now')) ?></td>
                    <td class="d-flex gap-1 flex-wrap">
                        <a href="<?= Url::to(['/admin/order-view', 'id' => $order->id]) ?>" class="btn btn-sm btn-outline-secondary">Детали</a>
                        <?php if ($order->order_status_id === Order::STATUS_PENDING && !$departed): ?>
                        <form method="post" action="<?= Url::to(['/admin/update-order-status', 'id' => $order->id]) ?>" style="display:inline"
                              onsubmit="return confirm('Отметить заказ #<?= $order->id ?> как оплаченный?')">
                            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                            <input type="hidden" name="status_id" value="<?= Order::STATUS_PAID ?>">
                            <button type="submit" class="btn btn-sm btn-success">Оплачен</button>
                        </form>
                        <?php endif ?>
                        <?php if ($order->order_status_id !== Order::STATUS_CANCELLED && !$departed): ?>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#adminCancelModal<?= $order->id ?>">Отменить</button>
                        <div class="modal fade" id="adminCancelModal<?= $order->id ?>" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title">Отмена заказа #<?= $order->id ?></h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="post" action="<?= Url::to(['/admin/update-order-status', 'id' => $order->id]) ?>">
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
    <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $dataProvider->getPagination(), 'options' => ['class' => 'pagination justify-content-center mt-3']]) ?>
</div>
