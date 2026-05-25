<?php

/** @var yii\web\View $this */
/** @var app\models\Flight $model */
/** @var array $airports */
/** @var array $statuses */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use app\models\Flight;

$isNew = $model->isNewRecord;
$isPast = !$isNew && $model->arrival_time && strtotime($model->arrival_time) < time();
$this->title = $isNew ? 'Новый рейс' : 'Редактировать рейс';

// Убираем «Прибыл» из ручного выбора — статус выставляется автоматически
unset($statuses[Flight::STATUS_ARRIVED]);
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= Url::to(['/airline/index']) ?>">Мой кабинет</a></li>
            <li class="breadcrumb-item active"><?= Html::encode($this->title) ?></li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-4">
                <h5 class="fw-bold mb-4"><?= Html::encode($this->title) ?></h5>

                <?php if ($isPast): ?>
                <div class="alert alert-secondary mb-3">
                    Рейс уже выполнен — редактирование недоступно.
                </div>
                <?php endif ?>

                <?php $form = ActiveForm::begin(['options' => $isPast ? ['onsubmit' => 'return false;'] : []]); ?>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'flight_number')->textInput(['placeholder' => 'SU-123']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'flight_status_id')->dropDownList($statuses, ['prompt' => 'Статус']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'departure_airport_id')->dropDownList($airports, ['prompt' => 'Выберите аэропорт']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'arrival_airport_id')->dropDownList($airports, ['prompt' => 'Выберите аэропорт']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'departure_time')->input('datetime-local') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'arrival_time')->input('datetime-local') ?>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <?php if (!$isPast): ?>
                    <?= Html::submitButton($isNew ? 'Создать рейс' : 'Сохранить', ['class' => 'btn btn-primary']) ?>
                    <?php endif ?>
                    <a href="<?= Url::to(['/airline/index']) ?>" class="btn btn-outline-secondary">Отмена</a>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
