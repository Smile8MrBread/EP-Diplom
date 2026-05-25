<?php

/** @var yii\web\View $this */
/** @var app\models\Airline $airline */
/** @var app\models\Flight[] $flights */
/** @var app\models\AirlineReview[] $reviews */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Airline;

$this->title = 'Кабинет авиакомпании';

$statusLabels = [
    Airline::STATUS_PENDING => ['badge-pending', 'На проверке'],
    Airline::STATUS_ACTIVE  => ['badge-active',  'Активна'],
    Airline::STATUS_BLOCKED => ['badge-blocked',  'Заблокирована'],
];
[$badgeClass, $statusText] = $statusLabels[$airline->airline_status_id] ?? ['badge-wait', '—'];
?>
<div class="container py-4">
    <?= \app\widgets\Alert::widget() ?>

    <!-- Airline info header -->
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4 class="fw-bold mb-1"><?= Html::encode($airline->name) ?></h4>
                <span class="text-muted small"><?= Html::encode($airline->country) ?></span>
                <span class="ms-2 <?= $badgeClass ?>"><?= $statusText ?></span>
            </div>
            <a href="<?= Url::to(['/airline/profile']) ?>" class="btn btn-outline-primary btn-sm">Редактировать</a>
        </div>

        <?php if ($airline->isPending): ?>
        <div class="alert alert-info mt-3 mb-0">
            Ваша авиакомпания ожидает подтверждения администратором. После одобрения вы сможете добавлять рейсы и билеты.
        </div>
        <?php endif ?>
    </div>

    <!-- Actions -->
    <?php if ($airline->isActive): ?>
    <div class="d-flex gap-2 mb-4">
        <a href="<?= Url::to(['/airline/create-flight']) ?>" class="btn btn-primary">+ Добавить рейс</a>
        <a href="<?= Url::to(['/airline/orders']) ?>" class="btn btn-outline-primary">📋 Заказы</a>
    </div>
    <?php endif ?>

    <?php
    $activeReviews  = array_values(array_filter($reviews, fn($r) => $r->deleted_at === null));
    $deletedReviews = array_values(array_filter($reviews, fn($r) => $r->deleted_at !== null));
    ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="airlineTabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-flights">Рейсы</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-reviews">Отзывы</button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Flights tab -->
        <div class="tab-pane fade show active" id="tab-flights">
            <?php if ($flights): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Номер</th><th>Маршрут</th><th>Вылет</th><th>Прилёт</th><th>Статус</th><th>Билетов</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($flights as $flight): ?>
                        <tr>
                            <td class="fw-semibold"><?= Html::encode($flight->flight_number) ?></td>
                            <td><?= Html::encode($flight->departureAirport->city) ?> → <?= Html::encode($flight->arrivalAirport->city) ?></td>
                            <td class="small"><?= date('d.m.Y H:i', strtotime($flight->departure_time)) ?></td>
                            <td class="small"><?= date('d.m.Y H:i', strtotime($flight->arrival_time)) ?></td>
                            <td><span class="badge bg-light text-secondary border"><?= Html::encode($flight->flightStatus->status) ?></span></td>
                            <td><?= count($flight->tickets) ?></td>
                            <td>
                                <a href="<?= Url::to(['/airline/flight', 'id' => $flight->id]) ?>" class="btn btn-sm btn-outline-primary">Детали</a>
                                <a href="<?= Url::to(['/airline/update-flight', 'id' => $flight->id]) ?>" class="btn btn-sm btn-outline-secondary ms-1">Изм.</a>
                            </td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="card p-4 text-center text-muted">
                <div style="font-size:2.5rem">✈</div>
                <p class="mt-2">Рейсов пока нет. <?= $airline->isActive ? Html::a('Добавить первый рейс', ['/airline/create-flight'], ['class' => 'text-primary']) : '' ?></p>
            </div>
            <?php endif ?>
        </div>

        <!-- Reviews tab -->
        <div class="tab-pane fade" id="tab-reviews">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-semibold">Отзывы о вашей авиакомпании</span>
                <?php if ($deletedReviews): ?>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-primary" id="btn-show-active" onclick="switchReviews('active')">
                        Активные
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btn-show-deleted" onclick="switchReviews('deleted')">
                        Удалённые (<?= count($deletedReviews) ?>)
                    </button>
                </div>
                <?php endif ?>
            </div>

            <div id="reviews-active">
                <?php if ($activeReviews): ?>
                    <?php foreach ($activeReviews as $review): ?>
                    <div class="card mb-2 p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong class="small"><?= Html::encode($review->rating->user->getFullName()) ?></strong>
                                <span class="stars ms-1"><?= str_repeat('★', $review->rating->rating_value) ?></span>
                            </div>
                            <span class="text-muted small"><?= date('d.m.Y H:i', strtotime($review->created_at)) ?></span>
                        </div>
                        <p class="small mb-0 mt-1 text-muted"><?= Html::encode($review->text) ?></p>
                    </div>
                    <?php endforeach ?>
                <?php else: ?>
                    <p class="text-muted small">Активных отзывов нет.</p>
                <?php endif ?>
            </div>

            <div id="reviews-deleted" style="display:none">
                <?php foreach ($deletedReviews as $review): ?>
                <div class="card mb-2 p-3 border-danger border-opacity-25">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong class="small"><?= Html::encode($review->rating->user->getFullName()) ?></strong>
                            <span class="stars ms-1"><?= str_repeat('★', $review->rating->rating_value) ?></span>
                        </div>
                        <span class="text-muted small"><?= date('d.m.Y H:i', strtotime($review->created_at)) ?></span>
                    </div>
                    <p class="small mb-2 mt-1 text-muted"><?= Html::encode($review->text) ?></p>
                    <div class="small text-danger">
                        Удалён: <?= date('d.m.Y H:i', strtotime($review->deleted_at)) ?>
                        <?php if ($review->delete_reason): ?>
                        · Причина: <?= Html::encode($review->delete_reason) ?>
                        <?php endif ?>
                    </div>
                </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
</div>

