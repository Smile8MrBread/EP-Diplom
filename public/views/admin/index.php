<?php

/** @var yii\web\View $this */
/** @var array $stats */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Панель администратора';
?>
<div class="container py-4">
    <h4 class="fw-bold mb-4">Панель администратора</h4>

    <?= \app\widgets\Alert::widget() ?>

    <!-- Stats -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="<?= Url::to(['/admin/users']) ?>" class="text-decoration-none">
                <div class="card p-4 text-center">
                    <div style="font-size:2rem;color:var(--ab-blue)">👥</div>
                    <div class="display-6 fw-bold mt-1"><?= $stats['users'] ?></div>
                    <div class="text-muted small">Пассажиров</div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= Url::to(['/admin/airlines']) ?>" class="text-decoration-none">
                <div class="card p-4 text-center position-relative">
                    <div style="font-size:2rem;color:var(--ab-blue)">✈</div>
                    <div class="display-6 fw-bold mt-1"><?= $stats['airlines'] ?></div>
                    <div class="text-muted small">Авиакомпаний</div>
                    <?php if ($stats['pending'] > 0): ?>
                    <span class="position-absolute top-0 end-0 m-2 badge bg-danger"><?= $stats['pending'] ?></span>
                    <?php endif ?>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= Url::to(['/admin/orders']) ?>" class="text-decoration-none">
                <div class="card p-4 text-center">
                    <div style="font-size:2rem;color:var(--ab-blue)">📋</div>
                    <div class="display-6 fw-bold mt-1"><?= $stats['orders'] ?></div>
                    <div class="text-muted small">Заказов</div>
                </div>
            </a>
        </div>
    </div>
</div>
