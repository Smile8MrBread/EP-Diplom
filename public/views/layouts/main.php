<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\models\User;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);

$user = Yii::$app->user;
/** @var User|null $identity */
$identity = $user->identity;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="ru" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?> — AirBook</title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header>
    <?php
    NavBar::begin([
        'brandLabel' => '<span>Air</span>Book &#9992;',
        'brandUrl'   => Yii::$app->homeUrl,
        'options'    => ['class' => 'navbar navbar-expand-md navbar-dark fixed-top'],
        'innerContainerOptions' => ['class' => 'container'],
    ]);

    $navItems = [
        ['label' => 'Каталог', 'url' => ['/catalog/index']],
    ];

    if ($user->isGuest) {
        $navItems[] = ['label' => 'Войти',        'url' => ['/site/login']];
        $navItems[] = ['label' => 'Регистрация',  'url' => ['/site/reg']];
        $navItems[] = ['label' => 'Для авиакомпаний', 'url' => ['/site/reg-airline']];
    } elseif ($identity->isAdmin) {
        $navItems[] = ['label' => 'Панель Admin', 'url' => ['/admin/index']];
    } elseif ($identity->isAirline) {
        $navItems[] = ['label' => 'Мой кабинет', 'url' => ['/airline/index']];
    } else {
        $navItems[] = ['label' => 'Мои заказы',  'url' => ['/account/index']];
        $navItems[] = ['label' => 'Избранное',   'url' => ['/account/favorites']];
    }

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav ms-auto'],
        'items'   => $navItems,
    ]);

    if (!$user->isGuest) {
        echo '<div class="d-flex align-items-center ms-3 me-1">';
        echo '<span class="text-white-50 small">' . Html::encode($identity->getFullName()) . '</span>';
        echo '</div>';
        echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex align-items-center']);
        echo Html::submitButton('Выход', ['class' => 'logout nav-link']);
        echo Html::endForm();
    }

    NavBar::end();
    ?>
</header>

<main id="main" class="flex-shrink-0" role="main" style="padding-top:56px;">
    <?= $content ?>
</main>

<footer class="mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-2">
                <strong class="text-white">Air<span style="color:var(--ab-gold)">Book</span> ✈</strong>
                <p class="small mt-1 mb-0">Онлайн-сервис бронирования авиабилетов</p>
            </div>
            <div class="col-md-4 mb-2">
                <p class="small mb-1"><strong class="text-white">Навигация</strong></p>
                <a href="/catalog/index">Каталог рейсов</a><br>
                <?php if ($user->isGuest): ?>
                    <a href="/site/login">Войти</a> · <a href="/site/reg">Регистрация</a>
                <?php endif ?>
            </div>
            <div class="col-md-4 text-md-end">
                <p class="small mb-0 mt-3">&copy; AirBook <?= date('Y') ?>. Все права защищены.</p>
            </div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
