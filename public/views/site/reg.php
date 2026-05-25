<?php

/** @var yii\web\View $this */
/** @var app\models\RegForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Регистрация';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <h3 class="fw-bold mb-1 text-center">Регистрация пассажира</h3>
                <p class="text-muted text-center small mb-4">Создайте аккаунт для бронирования билетов</p>

                <?php $form = ActiveForm::begin(['id' => 'reg-form']); ?>

                <div class="row">
                    <div class="col-md-6"><?= $form->field($model, 'first_name')->textInput(['placeholder' => 'Иван']) ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'last_name')->textInput(['placeholder' => 'Иванов']) ?></div>
                </div>

                <?= $form->field($model, 'email')->textInput(['placeholder' => 'ivan@mail.ru']) ?>
                <?= $form->field($model, 'phone')->textInput(['placeholder' => '+7 (999) 123-45-67']) ?>
                <?= $form->field($model, 'birth_date')->input('date') ?>
                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Минимум 6 символов']) ?>

                <div class="d-grid mt-3">
                    <?= Html::submitButton('Зарегистрироваться', ['class' => 'btn btn-primary py-2']) ?>
                </div>

                <?php ActiveForm::end(); ?>

                <hr>
                <p class="text-center small text-muted mb-0">
                    Уже есть аккаунт? <a href="<?= Url::to(['/site/login']) ?>">Войти</a>
                </p>
            </div>
        </div>
    </div>
</div>
