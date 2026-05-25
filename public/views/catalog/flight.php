<?php

/** @var yii\web\View $this */
/** @var app\models\Flight $flight */
/** @var int[] $favoriteIds */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $flight->departureAirport->city . ' → ' . $flight->arrivalAirport->city;
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= Url::to(['/catalog/index']) ?>">Каталог</a></li>
            <li class="breadcrumb-item active"><?= Html::encode($this->title) ?></li>
        </ol>
    </nav>

    <!-- Flight info card -->
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <a href="<?= Url::to(['/catalog/airline', 'id' => $flight->airline->id]) ?>"
                   class="fw-bold text-decoration-none" style="color:var(--ab-blue)">
                    <?= Html::encode($flight->airline->name) ?>
                </a>
                <span class="text-muted ms-2 small"><?= Html::encode($flight->flight_number) ?></span>
            </div>
            <span class="badge bg-light text-secondary border"><?= Html::encode($flight->flightStatus->status) ?></span>
        </div>

        <div class="row text-center mb-3">
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
    </div>

    <!-- Ticket types -->
    <h5 class="fw-bold mb-3">Выберите тип билета</h5>

    <?php
    $isAdmin = (bool)Yii::$app->user->identity?->isAdmin;
    $isUser  = (bool)Yii::$app->user->identity?->isUser;
    $canCancelAll = strtotime($flight->departure_time) > time() + 7 * 24 * 3600;
    ?>
    <?php if ($flight->tickets): ?>
        <?php foreach ($flight->tickets as $ticket): ?>
        <?php $ticketUrl = Url::to(['/catalog/ticket', 'id' => $ticket->id]) ?>
        <div class="card mb-3 ticket-card" style="cursor:pointer" onclick="window.location.href='<?= $ticketUrl ?>'">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-light text-secondary border"><?= Html::encode($ticket->cabinClass->class) ?></span>
                            <span class="fw-semibold small"><?= Html::encode($ticket->fare_type) ?></span>
                        </div>
                        <div class="text-muted small">
                            Багаж: <?= Html::encode($ticket->baggage_info) ?>
                            &nbsp;·&nbsp; Мест: <?= $ticket->available_quantity ?>
                        </div>
                    </div>
                    <div class="col-md-5 text-end">
                        <div class="ticket-price mb-2"><?= $ticket->getFormattedPrice() ?></div>
                        <div class="d-flex align-items-center justify-content-end gap-2" onclick="event.stopPropagation()">
                            <?php if ($isAdmin): ?>
                                <?php if ($canCancelAll && $ticket->ticket_status_id !== \app\models\Ticket::STATUS_CANCELLED): ?>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelTicket<?= $ticket->id ?>">Отменить</button>
                                <div class="modal fade" id="cancelTicket<?= $ticket->id ?>" tabindex="-1">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6 class="modal-title">Отмена билета</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="post" action="<?= Url::to(['/admin/cancel-ticket', 'id' => $ticket->id]) ?>">
                                                <div class="modal-body">
                                                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                                                    <p class="small text-muted mb-2">Все активные заказы по этому билету будут отменены.</p>
                                                    <label class="form-label small fw-semibold">Причина отмены <span class="text-danger">*</span></label>
                                                    <textarea name="reason" class="form-control form-control-sm" rows="2" placeholder="Укажите причину" required></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Назад</button>
                                                    <button type="submit" class="btn btn-sm btn-danger">Отменить билет</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php elseif ($ticket->ticket_status_id === \app\models\Ticket::STATUS_CANCELLED): ?>
                                <span class="text-danger small">Отменён</span>
                                <?php else: ?>
                                <span class="text-muted small">Нельзя отменить</span>
                                <?php endif ?>
                            <?php endif ?>
                            <?php if ($isUser): ?>
                            <button class="fav-btn fav-ticket-btn <?= in_array($ticket->id, $favoriteIds) ? 'active' : '' ?>"
                                    data-url="<?= Url::to(['/catalog/toggle-favorite-ticket', 'id' => $ticket->id]) ?>"
                                    title="В избранное">♥</button>
                            <?php endif ?>
                            <a href="<?= $ticketUrl ?>" class="btn btn-primary btn-sm">Подробнее →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach ?>
    <?php else: ?>
        <div class="card p-5 text-center text-muted">
            <p class="mb-0">Доступных билетов для этого рейса нет.</p>
        </div>
    <?php endif ?>

    <a href="<?= Url::to(['/catalog/index']) ?>" class="btn btn-outline-secondary btn-sm mt-2">← К поиску рейсов</a>
</div>

