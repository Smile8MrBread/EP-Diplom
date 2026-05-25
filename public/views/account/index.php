<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\AccountOrderSearch $searchModel */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Order;

$this->title = 'Мои заказы';

$statusClass = [
    Order::STATUS_PENDING   => 'badge-wait',
    Order::STATUS_PAID      => 'badge-paid',
    Order::STATUS_CANCELLED => 'badge-cancel',
];
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Мои заказы</h4>
        <div>
            <a href="<?= Url::to(['/account/profile']) ?>" class="btn btn-outline-secondary btn-sm me-2">Профиль</a>
            <a href="<?= Url::to(['/account/favorites']) ?>" class="btn btn-outline-secondary btn-sm">Избранное</a>
        </div>
    </div>

    <?= \app\widgets\Alert::widget() ?>

    <form method="get" action="<?= Url::to(['/account/index']) ?>" class="mb-3 d-flex gap-2 align-items-center flex-wrap">
        <select name="order_status_id" class="form-select form-select-sm" style="max-width:180px">
            <option value="">Все статусы</option>
            <option value="<?= Order::STATUS_PENDING ?>"   <?= $searchModel->order_status_id == Order::STATUS_PENDING   ? 'selected' : '' ?>>Ожидает оплаты</option>
            <option value="<?= Order::STATUS_PAID ?>"      <?= $searchModel->order_status_id == Order::STATUS_PAID      ? 'selected' : '' ?>>Оплачен</option>
            <option value="<?= Order::STATUS_CANCELLED ?>" <?= $searchModel->order_status_id == Order::STATUS_CANCELLED ? 'selected' : '' ?>>Отменён</option>
        </select>
        <button type="submit" class="btn btn-sm btn-primary">Фильтр</button>
        <?php if ($searchModel->order_status_id): ?>
        <a href="<?= Url::to(['/account/index']) ?>" class="btn btn-sm btn-outline-secondary">↺ Сбросить</a>
        <?php endif ?>
    </form>

    <?php
    $orders = $dataProvider->getModels();
    if (!$orders):
    ?>
        <div class="card p-5 text-center text-muted">
            <div style="font-size:3rem">📋</div>
            <p class="mt-2">У вас пока нет заказов.</p>
            <a href="<?= Url::to(['/catalog/index']) ?>" class="btn btn-primary">Найти рейсы</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Дата</th>
                        <th>Маршрут</th>
                        <th>Сумма</th>
                        <th>Оплата</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <?php $firstItem = $order->orderItems[0] ?? null; ?>
                    <tr>
                        <td class="fw-semibold">#<?= $order->id ?></td>
                        <td class="small text-muted"><?= date('d.m.Y', strtotime($order->date ?? 'now')) ?></td>
                        <td>
                            <?php if ($firstItem): ?>
                                <?php $flight = $firstItem->ticket->flight; ?>
                                <?= Html::encode($flight->departureAirport->city) ?> → <?= Html::encode($flight->arrivalAirport->city) ?>
                                <div class="text-muted small"><?= Html::encode($flight->airline->name) ?></div>
                            <?php endif ?>
                        </td>
                        <td class="fw-semibold"><?= $order->getFormattedTotal() ?></td>
                        <td class="small"><?= Html::encode($order->payType->title) ?></td>
                        <td><span class="<?= $statusClass[$order->order_status_id] ?? 'badge-wait' ?>"><?= Html::encode($order->orderStatus->status) ?></span></td>
                        <td>
                            <a href="<?= Url::to(['/account/order', 'id' => $order->id]) ?>" class="btn btn-sm btn-outline-primary">Детали</a>
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
