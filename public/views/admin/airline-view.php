<?php

/** @var yii\web\View $this */
/** @var app\models\Airline $airline */
/** @var app\models\AirlineReview[] $reviews */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Html::encode($airline->name);
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= Url::to(['/admin/index']) ?>">Панель</a></li>
            <li class="breadcrumb-item"><a href="<?= Url::to(['/admin/airlines']) ?>">Авиакомпании</a></li>
            <li class="breadcrumb-item active"><?= Html::encode($airline->name) ?></li>
        </ol>
    </nav>

    <?= \app\widgets\Alert::widget() ?>

    <div class="row">
        <div class="col-md-7">
            <div class="card p-4 mb-4">
                <h5 class="fw-bold mb-3"><?= Html::encode($airline->name) ?></h5>
                <dl class="row small">
                    <dt class="col-4 text-muted">Юр. название</dt><dd class="col-8"><?= Html::encode($airline->legal_name) ?></dd>
                    <dt class="col-4 text-muted">Страна</dt><dd class="col-8"><?= Html::encode($airline->country) ?></dd>
                    <dt class="col-4 text-muted">Email поддержки</dt><dd class="col-8"><?= Html::encode($airline->support_email) ?></dd>
                    <dt class="col-4 text-muted">Тел. поддержки</dt><dd class="col-8"><?= Html::encode($airline->support_phone) ?></dd>
                    <dt class="col-4 text-muted">Описание</dt><dd class="col-8"><?= nl2br(Html::encode($airline->description)) ?></dd>
                    <dt class="col-4 text-muted">Зарегистрирована</dt><dd class="col-8"><?= date('d.m.Y', strtotime($airline->created_at)) ?></dd>
                </dl>
            </div>

            <!-- Flights -->
            <h6 class="fw-bold mb-2">Рейсы (<?= count($airline->flights) ?>)</h6>
            <?php if ($airline->flights): ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Номер</th><th>Маршрут</th><th>Вылет</th><th>Статус</th></tr></thead>
                    <tbody>
                        <?php foreach ($airline->flights as $f): ?>
                        <tr>
                            <td><?= Html::encode($f->flight_number) ?></td>
                            <td><?= Html::encode($f->departureAirport->city) ?> → <?= Html::encode($f->arrivalAirport->city) ?></td>
                            <td class="small"><?= date('d.m.Y H:i', strtotime($f->departure_time)) ?></td>
                            <td><span class="badge bg-light text-secondary border"><?= Html::encode($f->flightStatus->status) ?></span></td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="text-muted small">Рейсов нет.</p>
            <?php endif ?>
        </div>

        <div class="col-md-5">
            <div class="card p-4">
                <h6 class="fw-bold mb-3">Владелец</h6>
                <?php $owner = $airline->ownerUser; ?>
                <?php if ($owner): ?>
                <dl class="small mb-3">
                    <dt class="text-muted">ФИО</dt><dd><?= Html::encode($owner->getFullName()) ?></dd>
                    <dt class="text-muted">Email</dt><dd><?= Html::encode($owner->email) ?></dd>
                    <dt class="text-muted">Телефон</dt><dd><?= Html::encode($owner->phone) ?></dd>
                </dl>
                <?php endif ?>

                <hr>
                <h6 class="fw-bold mb-3">Действия</h6>
                <div class="d-grid gap-2">
                    <?php if ($airline->isPending): ?>
                    <a href="<?= Url::to(['/admin/airline-approve', 'id' => $airline->id]) ?>"
                       class="btn btn-success" onclick="return confirm('Подтвердить?')">✓ Одобрить авиакомпанию</a>
                    <?php endif ?>
                    <a href="<?= Url::to(['/admin/airline-block', 'id' => $airline->id]) ?>"
                       class="btn <?= $airline->isBlocked ? 'btn-outline-success' : 'btn-outline-danger' ?>"
                       onclick="return confirm('<?= $airline->isBlocked ? 'Разблокировать?' : 'Заблокировать?' ?>')">
                        <?= $airline->isBlocked ? 'Разблокировать' : 'Заблокировать' ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php
    $activeReviews  = array_values(array_filter($reviews, fn($r) => $r->deleted_at === null));
    $deletedReviews = array_values(array_filter($reviews, fn($r) => $r->deleted_at !== null));
    ?>
    <div class="card p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Отзывы</h6>
            <?php if ($deletedReviews): ?>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-primary" id="adm-btn-active" onclick="admSwitchReviews('active')">Активные</button>
                <button type="button" class="btn btn-outline-secondary" id="adm-btn-deleted" onclick="admSwitchReviews('deleted')">Удалённые (<?= count($deletedReviews) ?>)</button>
            </div>
            <?php endif ?>
        </div>

        <div id="adm-reviews-active">
            <?php if ($activeReviews): ?>
                <?php foreach ($activeReviews as $review): ?>
                <div class="border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong class="small"><?= Html::encode($review->rating->user->getFullName()) ?></strong>
                            <span class="stars small ms-1"><?= str_repeat('★', $review->rating->rating_value) ?></span>
                        </div>
                        <span class="text-muted small"><?= date('d.m.Y H:i', strtotime($review->created_at)) ?></span>
                    </div>
                    <p class="small mb-0 mt-1 text-muted"><?= Html::encode($review->text) ?></p>
                </div>
                <?php endforeach ?>
            <?php else: ?>
                <p class="text-muted small mb-0">Активных отзывов нет.</p>
            <?php endif ?>
        </div>

        <div id="adm-reviews-deleted" style="display:none">
            <?php if ($deletedReviews): ?>
                <?php foreach ($deletedReviews as $review): ?>
                <div class="border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong class="small"><?= Html::encode($review->rating->user->getFullName()) ?></strong>
                            <span class="stars small ms-1"><?= str_repeat('★', $review->rating->rating_value) ?></span>
                        </div>
                        <span class="text-muted small"><?= date('d.m.Y H:i', strtotime($review->created_at)) ?></span>
                    </div>
                    <p class="small mb-1 mt-1 text-muted"><?= Html::encode($review->text) ?></p>
                    <div class="small text-danger">
                        Удалён: <?= date('d.m.Y H:i', strtotime($review->deleted_at)) ?>
                        <?php if ($review->delete_reason): ?>
                        · Причина: <?= Html::encode($review->delete_reason) ?>
                        <?php endif ?>
                        <?php if ($review->moderatedBy): ?>
                        · Модератор: <?= Html::encode($review->moderatedBy->getFullName()) ?>
                        <?php endif ?>
                    </div>
                </div>
                <?php endforeach ?>
            <?php else: ?>
                <p class="text-muted small mb-0">Удалённых отзывов нет.</p>
            <?php endif ?>
        </div>
    </div>
</div>

