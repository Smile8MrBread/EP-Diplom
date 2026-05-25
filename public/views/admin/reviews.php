<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Модерация отзывов';
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Модерация отзывов</h4>
        <a href="<?= Url::to(['/admin/index']) ?>" class="btn btn-outline-secondary btn-sm">← Панель</a>
    </div>

    <?= \app\widgets\Alert::widget() ?>

    <?php $reviews = $dataProvider->getModels(); ?>
    <?php if (!$reviews): ?>
        <div class="card p-4 text-center text-muted">Отзывов нет.</div>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
        <div class="card mb-3 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><?= Html::encode($review->rating->user->getFullName()) ?></strong>
                    <span class="text-muted ms-2 small">о <?= Html::encode($review->rating->airline->name) ?></span>
                    <span class="stars ms-2"><?= str_repeat('★', $review->rating->rating_value) ?></span>
                </div>
                <span class="text-muted small"><?= date('d.m.Y', strtotime($review->created_at)) ?></span>
            </div>
            <p class="mt-2 mb-2"><?= Html::encode($review->text) ?></p>
            <form method="post" action="<?= Url::to(['/admin/delete-review', 'id' => $review->id]) ?>" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                <input type="text" name="reason" class="form-control form-control-sm" placeholder="Причина удаления" style="max-width:300px">
                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить отзыв?')">Удалить</button>
            </form>
        </div>
        <?php endforeach ?>
        <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $dataProvider->getPagination(), 'options' => ['class' => 'pagination justify-content-center mt-3']]) ?>
    <?php endif ?>
</div>
