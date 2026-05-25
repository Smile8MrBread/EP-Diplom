<?php

/** @var yii\web\View $this */
/** @var app\models\CatalogSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Airport;
use app\models\Airline;

$this->title = 'Каталог рейсов';
$airports  = Airport::getList();
$airlines  = Airline::find()->where(['airline_status_id' => Airline::STATUS_ACTIVE])->orderBy(['name' => SORT_ASC])->all();
?>
<div class="container py-4">

    <h4 class="fw-bold mb-4">Поиск рейсов</h4>

    <div class="row">
        <!-- Фильтры -->
        <div class="col-md-3 mb-4">
            <div class="filter-card">
                <h6 class="fw-bold mb-3">Фильтры</h6>
                <form id="filter-form" method="get" action="<?= Url::to(['/catalog/index']) ?>" data-disable-empty>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Откуда</label>
                        <select name="from" class="form-select form-select-sm">
                            <option value="">Любой</option>
                            <?php foreach ($airports as $id => $name): ?>
                                <option value="<?= $id ?>" <?= $searchModel->from == $id ? 'selected' : '' ?>>
                                    <?= Html::encode($name) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Куда</label>
                        <select name="to" class="form-select form-select-sm">
                            <option value="">Любой</option>
                            <?php foreach ($airports as $id => $name): ?>
                                <option value="<?= $id ?>" <?= $searchModel->to == $id ? 'selected' : '' ?>>
                                    <?= Html::encode($name) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Дата вылета</label>
                        <input type="date" name="date" class="form-control form-control-sm"
                               value="<?= Html::encode($searchModel->date) ?>" min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Авиакомпания</label>
                        <select name="airline_id" class="form-select form-select-sm">
                            <option value="">Любая</option>
                            <?php foreach ($airlines as $airline): ?>
                                <option value="<?= $airline->id ?>" <?= $searchModel->airline_id == $airline->id ? 'selected' : '' ?>>
                                    <?= Html::encode($airline->name) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Применить</button>
                    <a href="<?= Url::to(['/catalog/index']) ?>" class="btn btn-outline-secondary btn-sm w-100 mt-2">↺ Сбросить</a>
                </form>
            </div>
        </div>

        <!-- Результаты -->
        <div class="col-md-9">
            <?php
            $models = $dataProvider->getModels();
            $total  = $dataProvider->getTotalCount();
            ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small">Найдено: <strong><?= $total ?></strong> рейсов</span>
                <div class="d-flex gap-1">
                    <?php
                    $sortOptions = ['price_asc' => 'Цена ↑', 'price_desc' => 'Цена ↓', 'date_asc' => 'Дата ↑', 'date_desc' => 'Дата ↓'];
                    $currentSort = $searchModel->sort ?: 'date_asc';
                    $baseParams  = array_filter(['from' => $searchModel->from, 'to' => $searchModel->to, 'date' => $searchModel->date, 'airline_id' => $searchModel->airline_id]);
                    foreach ($sortOptions as $val => $label):
                        $isActive = $currentSort === $val;
                        $url = Url::to(array_merge(['/catalog/index'], $baseParams, $val !== 'date_asc' ? ['sort' => $val] : []));
                    ?>
                    <a href="<?= $url ?>" class="btn btn-sm <?= $isActive ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $label ?></a>
                    <?php endforeach ?>
                </div>
            </div>

            <?php if (!$models): ?>
                <div class="card p-5 text-center text-muted">
                    <div style="font-size:3rem">✈</div>
                    <p class="mt-2">По вашему запросу рейсы не найдены. Попробуйте изменить параметры поиска.</p>
                </div>
            <?php else: ?>
                <?php $isAdmin = (bool)Yii::$app->user->identity?->isAdmin; ?>
                <?php foreach ($models as $flight): ?>
                <?php
                $classes = array_unique(array_map(fn($t) => $t->cabinClass->class, $flight->tickets));
                $minPrice = $flight->min_price ? number_format((int)$flight->min_price, 0, '.', ' ') . ' ₽' : '—';
                ?>
                <?php $flightUrl = Url::to(['/catalog/flight', 'id' => $flight->id]) ?>
                <div class="card mb-3 ticket-card" style="cursor:pointer" onclick="window.location.href='<?= $flightUrl ?>'">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <a href="<?= Url::to(['/catalog/airline', 'id' => $flight->airline->id]) ?>"
                                       class="fw-semibold text-decoration-none" style="color:var(--ab-blue)"
                                       onclick="event.stopPropagation()">
                                        <?= Html::encode($flight->airline->name) ?>
                                    </a>
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
                                <div class="text-muted small mb-1">от</div>
                                <div class="ticket-price mb-2"><?= $minPrice ?></div>
                                <div class="d-flex align-items-center justify-content-end gap-2" onclick="event.stopPropagation()">
                                    <?php if ($isAdmin && $flight->flight_status_id !== \app\models\Flight::STATUS_CANCELLED): ?>
                                        <?php if ($flight->canCancelByRule()): ?>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelFlight<?= $flight->id ?>">Отменить</button>
                                        <div class="modal fade" id="cancelFlight<?= $flight->id ?>" tabindex="-1">
                                            <div class="modal-dialog modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h6 class="modal-title">Отмена рейса</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="post" action="<?= Url::to(['/admin/cancel-flight', 'id' => $flight->id]) ?>">
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
                                        <span class="text-muted small" title="Есть активные заказы и до вылета менее 3 месяцев">Нельзя отменить</span>
                                        <?php endif ?>
                                    <?php endif ?>
                                    <a href="<?= $flightUrl ?>" class="btn btn-primary btn-sm">Выбрать →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach ?>

                <?= \yii\bootstrap5\LinkPager::widget([
                    'pagination' => $dataProvider->getPagination(),
                    'options'    => ['class' => 'pagination justify-content-center mt-4'],
                ]) ?>
            <?php endif ?>
        </div>
    </div>
</div>
