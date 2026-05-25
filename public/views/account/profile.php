<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Мой профиль';
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="profile-avatar"><?= mb_strtoupper(mb_substr($user->first_name, 0, 1)) ?></div>
                    <div>
                        <h5 class="fw-bold mb-0"><?= Html::encode($user->getFullName()) ?></h5>
                        <span class="text-muted small"><?= Html::encode($user->email) ?></span>
                    </div>
                </div>

                <?= \app\widgets\Alert::widget() ?>

                <form method="post">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Имя</label>
                            <input type="text" name="first_name" class="form-control" value="<?= Html::encode($user->first_name) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Фамилия</label>
                            <input type="text" name="last_name" class="form-control" value="<?= Html::encode($user->last_name) ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-muted">(нельзя изменить)</span></label>
                        <input type="email" class="form-control" value="<?= Html::encode($user->email) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Телефон</label>
                        <input type="text" name="phone" class="form-control" value="<?= Html::encode($user->phone) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Дата рождения</label>
                        <input type="date" name="birth_date" class="form-control" value="<?= Html::encode($user->birth_date) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Сохранить</button>
                </form>
            </div>
        </div>
    </div>
</div>
