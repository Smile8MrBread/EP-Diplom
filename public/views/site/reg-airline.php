<?php

/** @var yii\web\View $this */
/** @var app\models\AirlineRegForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Регистрация авиакомпании';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-4">
                <h3 class="fw-bold mb-1 text-center">Регистрация авиакомпании</h3>
                <p class="text-muted text-center small mb-4">
                    После регистрации ваша заявка будет рассмотрена администратором.
                    До подтверждения управление рейсами недоступно.
                </p>

                <?php $form = ActiveForm::begin(['id' => 'airline-reg-form']); ?>

                <h6 class="fw-bold text-primary mb-3 mt-2">Данные представителя</h6>
                <div class="row">
                    <div class="col-md-6"><?= $form->field($model, 'first_name')->textInput(['placeholder' => 'Имя']) ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'last_name')->textInput(['placeholder' => 'Фамилия']) ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?= $form->field($model, 'email')->textInput(['placeholder' => 'Email для входа']) ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'phone')->textInput(['placeholder' => '+7 (999) 000-00-00']) ?></div>
                </div>
                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Минимум 6 символов']) ?>

                <h6 class="fw-bold text-primary mb-3 mt-3">Данные авиакомпании</h6>
                <div class="row">
                    <div class="col-md-6"><?= $form->field($model, 'airline_name')->textInput(['placeholder' => 'S7 Airlines']) ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'legal_name')->textInput(['placeholder' => 'ООО «Авиакомпания»']) ?></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><?= $form->field($model, 'country')->textInput(['placeholder' => 'Россия']) ?></div>
                    <div class="col-md-4"><?= $form->field($model, 'support_email')->textInput(['placeholder' => 'support@airline.ru']) ?></div>
                    <div class="col-md-4"><?= $form->field($model, 'support_phone')->textInput(['placeholder' => '8-800-000-00-00']) ?></div>
                </div>
                <?= $form->field($model, 'description')->textarea(['rows' => 4, 'placeholder' => 'Краткое описание авиакомпании']) ?>

                <div class="d-grid mt-3">
                    <?= Html::submitButton('Подать заявку', ['class' => 'btn btn-primary py-2']) ?>
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
