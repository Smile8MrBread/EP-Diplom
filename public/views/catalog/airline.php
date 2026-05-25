<?php

/** @var yii\web\View $this */
/** @var app\models\Airline $airline */
/** @var bool $isFavorite */
/** @var app\models\AirlineRating|null $userRating */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Html::encode($airline->name);
$avgRating   = $airline->averageRating;
$reviews     = $airline->reviews;
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= Url::to(['/catalog/index']) ?>">Каталог</a></li>
            <li class="breadcrumb-item active"><?= Html::encode($airline->name) ?></li>
            <?php if (Yii::$app->user->identity?->isAdmin): ?>
            <li class="breadcrumb-item ms-auto">
                <a href="<?= Url::to(['/admin/airline-view', 'id' => $airline->id]) ?>" class="btn btn-sm btn-outline-secondary py-0">⚙ Детали (Админ)</a>
            </li>
            <?php endif ?>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <!-- Airline header -->
            <div class="card mb-4 p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="fw-bold mb-1"><?= Html::encode($airline->name) ?></h3>
                        <span class="text-muted small"><?= Html::encode($airline->legal_name) ?> · <?= Html::encode($airline->country) ?></span>
                        <div class="mt-2">
                            <span class="stars"><?= str_repeat('★', (int)round($avgRating)) ?><?= str_repeat('☆', 5 - (int)round($avgRating)) ?></span>
                            <span class="text-muted ms-1"><?= $avgRating > 0 ? $avgRating : 'нет оценок' ?></span>
                        </div>
                    </div>
                    <?php if (Yii::$app->user->identity?->isUser): ?>
                    <button class="fav-btn <?= $isFavorite ? 'active' : '' ?>" id="fav-airline"
                            data-url="<?= Url::to(['/catalog/toggle-favorite-airline', 'id' => $airline->id]) ?>"
                            title="В избранное">♥</button>
                    <?php endif ?>
                </div>
                <p class="mt-3 mb-0"><?= nl2br(Html::encode($airline->description)) ?></p>
                <div class="row mt-3 text-muted small">
                    <div class="col-md-6"><strong>Email:</strong> <?= Html::encode($airline->support_email) ?></div>
                    <div class="col-md-6"><strong>Телефон:</strong> <?= Html::encode($airline->support_phone) ?></div>
                </div>
            </div>

            <!-- Flights -->
            <?php
            $isAdmin = (bool)Yii::$app->user->identity?->isAdmin;
            $flights = array_values(array_filter($airline->flights, function($f) {
                return strtotime($f->departure_time) > time()
                    && $f->flight_status_id !== \app\models\Flight::STATUS_CANCELLED;
            }));
            ?>
            <h5 class="fw-bold mb-3">Рейсы авиакомпании</h5>
            <?php if ($flights): ?>
                <?php foreach ($flights as $flight): ?>
                <?php
                $availableTickets = array_values(array_filter($flight->tickets, fn($t) =>
                    $t->ticket_status_id === \app\models\Ticket::STATUS_AVAILABLE && $t->available_quantity > 0
                ));
                $prices   = array_map(fn($t) => $t->price, $availableTickets);
                $minPrice = $prices ? number_format((int)min($prices), 0, '.', ' ') . ' ₽' : null;
                $classes  = array_unique(array_map(fn($t) => $t->cabinClass->class, $availableTickets));
                $flightUrl = Url::to(['/catalog/flight', 'id' => $flight->id]);
                ?>
                <div class="card mb-3 ticket-card" style="cursor:pointer" onclick="window.location.href='<?= $flightUrl ?>'">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="text-muted small"><?= Html::encode($flight->flight_number) ?></span>
                                </div>
                                <div class="route-line">
                                    <span class="city"><?= Html::encode($flight->departureAirport->city) ?></span>
                                    <span class="arrow">→</span>
                                    <span class="city"><?= Html::encode($flight->arrivalAirport->city) ?></span>
                                    <span class="dur"><?= $flight->durationFormatted ?></span>
                                </div>
                                <div class="text-muted small mt-1">
                                    <?= date('d.m.Y H:i', strtotime($flight->departure_time)) ?>
                                    &nbsp;—&nbsp;
                                    <?= date('d.m.Y H:i', strtotime($flight->arrival_time)) ?>
                                </div>
                                <?php if ($classes): ?>
                                <div class="mt-2">
                                    <?php foreach ($classes as $class): ?>
                                    <span class="badge bg-light text-secondary border me-1"><?= Html::encode($class) ?></span>
                                    <?php endforeach ?>
                                </div>
                                <?php endif ?>
                            </div>
                            <div class="col-md-5 text-end">
                                <?php if ($minPrice): ?>
                                <div class="text-muted small mb-1">от</div>
                                <div class="ticket-price mb-2"><?= $minPrice ?></div>
                                <?php endif ?>
                                <div class="d-flex align-items-center justify-content-end gap-2" onclick="event.stopPropagation()">
                                    <?php if ($isAdmin): ?>
                                    <a href="<?= Url::to(['/admin/flight', 'id' => $flight->id]) ?>" class="btn btn-sm btn-outline-secondary">⚙</a>
                                    <?php endif ?>
                                    <a href="<?= $flightUrl ?>" class="btn btn-primary btn-sm">Выбрать →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach ?>
            <?php else: ?>
                <p class="text-muted">Рейсов пока нет.</p>
            <?php endif ?>
        </div>

        <!-- Sidebar: rating & reviews -->
        <div class="col-md-4">
            <?php if (Yii::$app->user->identity?->isUser): ?>
            <div class="card mb-4 p-3">
                <h6 class="fw-bold mb-3">Ваша оценка</h6>
                <div id="star-container" class="mb-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button class="star-btn <?= ($userRating && $userRating->rating_value >= $i) ? 'active' : '' ?>"
                            data-value="<?= $i ?>"
                            data-url="<?= Url::to(['/catalog/rate', 'airlineId' => $airline->id]) ?>">★</button>
                    <?php endfor ?>
                </div>
                <p class="text-muted small mb-3" id="avg-text">Средняя: <?= $avgRating ?: '—' ?></p>

                <?php if ($userRating): ?>
                    <?php $review = $userRating->review; ?>
                    <?php if ($review && $review->deleted_at): ?>
                    <div class="alert alert-danger py-2 px-3 small mb-0">
                        <strong>Ваш отзыв удалён</strong><br>
                        <?= date('d.m.Y H:i', strtotime($review->deleted_at)) ?>
                        <?php if ($review->delete_reason): ?>
                        · <?= Html::encode($review->delete_reason) ?>
                        <?php endif ?>
                    </div>
                    <?php else: ?>
                    <form method="post" action="<?= Url::to(['/catalog/review', 'airlineId' => $airline->id]) ?>">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                        <textarea name="text" class="form-control form-control-sm mb-2" rows="3"
                                  placeholder="Напишите отзыв..."><?= $review ? Html::encode($review->text) : '' ?></textarea>
                        <button type="submit" class="btn btn-primary btn-sm w-100">Сохранить отзыв</button>
                    </form>
                    <?php endif ?>
                <?php else: ?>
                    <p class="text-muted small">Поставьте оценку, чтобы написать отзыв.</p>
                <?php endif ?>
            </div>
            <?php endif ?>

            <!-- Reviews list -->
            <div class="card p-3">
                <h6 class="fw-bold mb-3">Отзывы (<?= count($reviews) ?>)</h6>
                <?php if ($reviews): ?>
                    <?php foreach (array_slice($reviews, 0, 5) as $review): ?>
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong class="small"><?= Html::encode($review->rating->user->getFullName()) ?></strong>
                                <span class="stars small ms-1"><?= str_repeat('★', $review->rating->rating_value) ?></span>
                            </div>
                            <?php if (Yii::$app->user->identity?->isAdmin): ?>
                            <form method="post" action="<?= Url::to(['/admin/delete-review', 'id' => $review->id]) ?>">
                                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                                <input type="hidden" name="ref" value="airline">
                                <input type="hidden" name="airline_id" value="<?= $airline->id ?>">
                                <input type="text" name="reason" class="form-control form-control-sm d-inline" style="width:130px;font-size:.7rem" placeholder="Причина">
                                <button type="submit" class="btn btn-outline-danger ms-1" onclick="return confirm('Удалить?')" style="font-size:1rem;line-height:1">🗑</button>
                            </form>
                            <?php endif ?>
                        </div>
                        <p class="small mb-0 text-muted"><?= Html::encode($review->text) ?></p>
                    </div>
                    <?php endforeach ?>
                <?php else: ?>
                    <p class="text-muted small">Отзывов пока нет.</p>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

