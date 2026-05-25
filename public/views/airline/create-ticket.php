<?php

/** @var yii\web\View $this */
/** @var app\models\Ticket $model */
/** @var app\models\Flight $flight */
/** @var array $cabinClasses */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'Новый билет' : 'Редактировать билет';
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= Url::to(['/airline/index']) ?>">Кабинет</a></li>
            <li class="breadcrumb-item"><a href="<?= Url::to(['/airline/flight', 'id' => $flight->id]) ?>"><?= Html::encode($flight->flight_number) ?></a></li>
            <li class="breadcrumb-item active"><?= Html::encode($this->title) ?></li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-7">
            <!-- Flight info -->
            <div class="card p-3 mb-3 bg-light border-0">
                <div class="route-line">
                    <span class="city"><?= Html::encode($flight->departureAirport->city) ?></span>
                    <span class="arrow">→</span>
                    <span class="city"><?= Html::encode($flight->arrivalAirport->city) ?></span>
                    <span class="dur"><?= date('d.m.Y H:i', strtotime($flight->departure_time)) ?></span>
                </div>
            </div>

            <div class="card p-4">
                <h5 class="fw-bold mb-4"><?= Html::encode($this->title) ?></h5>

                <?php $form = ActiveForm::begin(); ?>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'cabin_class_id')->dropDownList($cabinClasses, ['prompt' => 'Класс салона']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'fare_type')->textInput(['placeholder' => 'Базовый, Бизнес...']) ?>
                    </div>
                </div>
                <?= $form->field($model, 'baggage_info')->textInput(['placeholder' => '1 место 23 кг']) ?>
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'price')->textInput(['type' => 'number', 'min' => 0, 'step' => '0.01']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'currency_code')->textInput(['placeholder' => 'RUB', 'maxlength' => 3]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'available_quantity')->textInput(['type' => 'number', 'min' => 0]) ?>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <?= Html::submitButton($isNew ? 'Добавить билет' : 'Сохранить', ['class' => 'btn btn-primary']) ?>
                    <a href="<?= Url::to(['/airline/flight', 'id' => $flight->id]) ?>" class="btn btn-outline-secondary">Отмена</a>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
