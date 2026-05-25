<?php

/** @var yii\web\View $this */
/** @var app\models\Airline $airline */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Профиль авиакомпании';
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Профиль авиакомпании</h5>
                <a href="<?= Url::to(['/airline/index']) ?>" class="btn btn-outline-secondary btn-sm">← Кабинет</a>
            </div>

            <?= \app\widgets\Alert::widget() ?>

            <div class="card p-4">
                <?php $form = ActiveForm::begin(); ?>

                <div class="row">
                    <div class="col-md-6"><?= $form->field($airline, 'name') ?></div>
                    <div class="col-md-6"><?= $form->field($airline, 'legal_name') ?></div>
                </div>
                <?= $form->field($airline, 'country') ?>
                <div class="row">
                    <div class="col-md-6"><?= $form->field($airline, 'support_email') ?></div>
                    <div class="col-md-6"><?= $form->field($airline, 'support_phone') ?></div>
                </div>
                <?= $form->field($airline, 'description')->textarea(['rows' => 5]) ?>

                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
