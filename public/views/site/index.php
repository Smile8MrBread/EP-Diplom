<?php

/** @var yii\web\View $this */
/** @var array $airports */
/** @var app\models\Airline[] $airlines */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Главная';
?>

<!-- Hero -->
<div class="hero">
    <div class="container text-center">
        <h1>✈ Найдите свой рейс</h1>
        <p class="lead mb-0">Тысячи направлений по лучшим ценам. Бронируйте быстро и удобно.</p>
    </div>
</div>

<!-- Search block -->
<div class="container">
    <div class="search-card mb-5">
        <form id="hero-search" action="<?= Url::to(['/catalog/index']) ?>" method="get" data-disable-empty>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Откуда</label>
                    <select name="from" class="form-select">
                        <option value="">Любой город</option>
                        <?php foreach ($airports as $id => $name): ?>
                            <option value="<?= $id ?>"><?= Html::encode($name) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Куда</label>
                    <select name="to" class="form-select">
                        <option value="">Любой город</option>
                        <?php foreach ($airports as $id => $name): ?>
                            <option value="<?= $id ?>"><?= Html::encode($name) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Дата вылета</label>
                    <input type="date" name="date" class="form-control" min="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        Найти рейсы
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Features -->
    <div class="row text-center mb-5">
        <div class="col-md-4 mb-4">
            <div class="feature-icon mx-auto">✈</div>
            <h5 class="fw-bold">Широкий выбор</h5>
            <p class="text-muted small">Рейсы крупнейших авиакомпаний России и мира в одном месте.</p>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-icon mx-auto">💳</div>
            <h5 class="fw-bold">Быстрая оплата</h5>
            <p class="text-muted small">Оплата картой, СБП или наличными. Мгновенное подтверждение.</p>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-icon mx-auto">⭐</div>
            <h5 class="fw-bold">Отзывы и рейтинги</h5>
            <p class="text-muted small">Читайте реальные отзывы пассажиров перед покупкой.</p>
        </div>
    </div>

    <!-- Airlines marquee -->
    <?php if ($airlines): ?>
    <h4 class="fw-bold mb-3">Авиакомпании</h4>
    <div class="airlines-marquee-wrap">
        <div class="airlines-marquee-track">
            <?php
            $ratings = [];
            foreach ($airlines as $a) { $ratings[$a->id] = $a->averageRating; }
            $cards = $airlines;
            while (count($cards) < 8) { $cards = array_merge($cards, $airlines); }
            $cards = array_merge($cards, $cards);
            foreach ($cards as $airline):
            ?>
            <a href="<?= Url::to(['/catalog/airline', 'id' => $airline->id]) ?>" class="airlines-marquee-card text-decoration-none">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div>
                        <div class="fw-bold small"><?= Html::encode($airline->name) ?></div>
                        <div class="text-muted marquee-meta"><?= Html::encode($airline->country) ?></div>
                    </div>
                    <?php $r = (int)round($ratings[$airline->id] ?? 0); ?>
                    <span class="stars"><?= str_repeat('★', $r) ?><?= str_repeat('☆', 5 - $r) ?></span>
                </div>
                <p class="text-muted mb-0 marquee-desc"><?= Html::encode(mb_substr($airline->description, 0, 70)) ?>…</p>
            </a>
            <?php endforeach ?>
        </div>
    </div>
    <div class="text-center mt-4 mb-5">
        <a href="<?= Url::to(['/catalog/index']) ?>" class="btn btn-outline-primary">Все рейсы →</a>
    </div>
    <?php endif ?>
</div>
