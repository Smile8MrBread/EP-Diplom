<?php

/** @var yii\web\View $this */
/** @var app\models\Flight $flight */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Ticket;

$this->title = 'Рейс ' . Html::encode($flight->flight_number);
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= Url::to(['/admin/index']) ?>">Панель</a></li>
            <li class="breadcrumb-item"><a href="<?= Url::to(['/catalog/airline', 'id' => $flight->airline_id]) ?>"><?= Html::encode($flight->airline->name) ?></a></li>
            <li class="breadcrumb-item active"><?= Html::encode($flight->flight_number) ?></li>
        </ol>
    </nav>

    <?= \app\widgets\Alert::widget() ?>

    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h5 class="fw-bold mb-0">Рейс <?= Html::encode($flight->flight_number) ?></h5>
            <span class="badge bg-secondary py-2 px-3"><?= Html::encode($flight->flightStatus->status) ?></span>
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
            <div class="col">Авиакомпания: <strong><?= Html::encode($flight->airline->name) ?></strong></div>
        </div>
    </div>

    <?php $canCancelTickets = strtotime($flight->departure_time) > time() + 7 * 24 * 3600; ?>
    <h5 class="fw-bold mb-3">Билеты (<?= count($flight->tickets) ?>)</h5>

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
                    <td>
                        <?php if ($ticket->ticket_status_id !== Ticket::STATUS_CANCELLED): ?>
                            <?php if ($canCancelTickets): ?>
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
                            <?php else: ?>
                            <span class="text-muted small" title="Отмена доступна не позднее чем за 7 дней до вылета">Нельзя отменить</span>
                            <?php endif ?>
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
        <p>Билетов для этого рейса нет.</p>
    </div>
    <?php endif ?>

    <a href="<?= Url::to(['/catalog/airline', 'id' => $flight->airline_id]) ?>" class="btn btn-outline-secondary btn-sm mt-2">← К авиакомпании</a>
</div>
