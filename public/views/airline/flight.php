<?php

/** @var yii\web\View $this */
/** @var app\models\Flight $flight */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Рейс ' . Html::encode($flight->flight_number);
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= Url::to(['/airline/index']) ?>">Мой кабинет</a></li>
            <li class="breadcrumb-item active"><?= Html::encode($flight->flight_number) ?></li>
        </ol>
    </nav>

    <?= \app\widgets\Alert::widget() ?>

    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h5 class="fw-bold mb-0">Рейс <?= Html::encode($flight->flight_number) ?></h5>
            <div class="d-flex gap-2">
                <?php if ($flight->flight_status_id !== \app\models\Flight::STATUS_CANCELLED && strtotime($flight->arrival_time) > time()): ?>
                <a href="<?= Url::to(['/airline/update-flight', 'id' => $flight->id]) ?>" class="btn btn-outline-secondary btn-sm">Изменить рейс</a>
                <?php if ($flight->canCancelByRule()): ?>
                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelFlightModal">Отменить рейс</button>
                <div class="modal fade" id="cancelFlightModal" tabindex="-1">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h6 class="modal-title">Отмена рейса</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post" action="<?= Url::to(['/airline/cancel-flight', 'id' => $flight->id]) ?>">
                                <div class="modal-body">
                                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                                    <p class="small text-muted mb-2">Все активные заказы по этому рейсу будут отменены.</p>
                                    <label class="form-label small fw-semibold">Причина отмены <span class="text-danger">*</span></label>
                                    <textarea name="reason" class="form-control form-control-sm" rows="2" placeholder="Укажите причину" required></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Назад</button>
                                    <button type="submit" class="btn btn-sm btn-danger">Отменить рейс</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <span class="text-muted small" title="Есть активные заказы и до вылета менее 3 месяцев">Отмена недоступна</span>
                <?php endif ?>
                <?php elseif ($flight->flight_status_id === \app\models\Flight::STATUS_CANCELLED): ?>
                <span class="badge bg-danger py-2 px-3">Рейс отменён</span>
                <?php endif ?>
            </div>
        </div>
        <div class="route-line mb-2">
            <span class="city"><?= Html::encode($flight->departureAirport->city) ?></span>
            <span class="arrow">→</span>
            <span class="city"><?= Html::encode($flight->arrivalAirport->city) ?></span>
            <span class="dur"><?= $flight->durationFormatted ?></span>
        </div>
        <div class="row text-muted small mt-2">
            <div class="col">Вылет: <?= date('d.m.Y H:i', strtotime($flight->departure_time)) ?></div>
            <div class="col">Прилёт: <?= date('d.m.Y H:i', strtotime($flight->arrival_time)) ?></div>
            <div class="col">Статус: <strong><?= Html::encode($flight->flightStatus->status) ?></strong></div>
        </div>
    </div>

    <!-- Tickets -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Билеты (<?= count($flight->tickets) ?>)</h5>
        <a href="<?= Url::to(['/airline/create-ticket', 'flightId' => $flight->id]) ?>" class="btn btn-primary btn-sm">+ Добавить билет</a>
    </div>

    <?php if ($flight->tickets): ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Класс</th>
                    <th>Тариф</th>
                    <th>Багаж</th>
                    <th>Цена</th>
                    <th>Мест</th>
                    <th>Статус</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($flight->tickets as $ticket): ?>
                <tr>
                    <td><?= Html::encode($ticket->cabinClass->class) ?></td>
                    <td><?= Html::encode($ticket->fare_type) ?></td>
                    <td><?= Html::encode($ticket->baggage_info) ?></td>
                    <td class="fw-semibold"><?= $ticket->getFormattedPrice() ?></td>
                    <td><?= $ticket->available_quantity ?></td>
                    <td><span class="badge bg-light text-secondary border"><?= Html::encode($ticket->ticketStatus->status) ?></span></td>
                    <td class="d-flex gap-1">
                        <?php if ($ticket->ticket_status_id !== \app\models\Ticket::STATUS_CANCELLED): ?>
                        <a href="<?= Url::to(['/airline/update-ticket', 'id' => $ticket->id]) ?>" class="btn btn-sm btn-outline-secondary">Изм.</a>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelTicket<?= $ticket->id ?>">Отменить</button>
                        <div class="modal fade" id="cancelTicket<?= $ticket->id ?>" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title">Отмена билета</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="post" action="<?= Url::to(['/airline/cancel-ticket', 'id' => $ticket->id]) ?>">
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
                        <?php else: ?>
                        <span class="text-danger small">Отменён</span>
                        <?php endif ?>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="card p-4 text-center text-muted">
        <p>Билетов для этого рейса пока нет.</p>
        <a href="<?= Url::to(['/airline/create-ticket', 'flightId' => $flight->id]) ?>" class="btn btn-primary btn-sm">Добавить билет</a>
    </div>
    <?php endif ?>
</div>
