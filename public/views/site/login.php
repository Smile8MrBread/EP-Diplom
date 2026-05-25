<?php

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Вход в аккаунт';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4">
                <h3 class="fw-bold mb-1 text-center">Вход</h3>
                <p class="text-muted text-center small mb-4">Введите ваш email и пароль</p>

                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                <?= $form->field($model, 'email')->textInput(['autofocus' => true, 'placeholder' => 'example@mail.ru']) ?>
                <?= $form->field($model, 'password')->passwordInput(['placeholder' => '••••••']) ?>
                <?= $form->field($model, 'rememberMe')->checkbox() ?>

                <div class="d-grid mt-3">
                    <?= Html::submitButton('Войти', ['class' => 'btn btn-primary py-2']) ?>
                </div>

                <?php ActiveForm::end(); ?>

                <hr>
                <p class="text-center small text-muted mb-0">
                    Нет аккаунта?
                    <a href="<?= Url::to(['/site/reg']) ?>">Зарегистрироваться</a> ·
                    <a href="<?= Url::to(['/site/reg-airline']) ?>">Я авиакомпания</a>
                </p>
            </div>

        </div>
    </div>
</div>
