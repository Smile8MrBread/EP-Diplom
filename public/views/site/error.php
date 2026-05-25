<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $name;
?>
<div class="container py-5 text-center">
    <div style="font-size:5rem">✈</div>
    <h1 class="fw-bold" style="color:var(--ab-blue)"><?= Html::encode($name) ?></h1>
    <p class="lead text-muted"><?= nl2br(Html::encode($message)) ?></p>
    <a href="<?= Url::home() ?>" class="btn btn-primary mt-3">На главную</a>
</div>
