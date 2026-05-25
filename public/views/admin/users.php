<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $search */
/** @var string $statusFilter */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\User;

$this->title = 'Пользователи';
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Пользователи</h4>
        <a href="<?= Url::to(['/admin/index']) ?>" class="btn btn-outline-secondary btn-sm">← Панель</a>
    </div>

    <?= \app\widgets\Alert::widget() ?>

    <form method="get" action="<?= Url::to(['/admin/users']) ?>" class="mb-3 d-flex gap-2 align-items-center flex-wrap">
        <input type="text" name="search" class="form-control form-control-sm" style="max-width:240px"
               placeholder="Email, имя или телефон" value="<?= Html::encode($search) ?>">
        <select name="status" class="form-select form-select-sm" style="max-width:160px">
            <option value="">Все статусы</option>
            <option value="active"  <?= $statusFilter === 'active'  ? 'selected' : '' ?>>Активные</option>
            <option value="blocked" <?= $statusFilter === 'blocked' ? 'selected' : '' ?>>Заблокированные</option>
        </select>
        <button type="submit" class="btn btn-sm btn-primary">Найти</button>
        <?php if ($search !== '' || $statusFilter !== ''): ?>
        <a href="<?= Url::to(['/admin/users']) ?>" class="btn btn-sm btn-outline-secondary">↺ Сбросить</a>
        <?php endif ?>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Телефон</th>
                    <th>Роль</th>
                    <th>Статус</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $user): ?>
                <tr>
                    <td><?= $user->id ?></td>
                    <td class="fw-semibold"><?= Html::encode($user->getFullName()) ?></td>
                    <td><?= Html::encode($user->email) ?></td>
                    <td><?= Html::encode($user->phone) ?></td>
                    <td>
                        <span class="badge bg-light text-secondary border"><?= Html::encode($user->role->role) ?></span>
                    </td>
                    <td>
                        <?php if ($user->isBlocked): ?>
                            <span class="badge-blocked">Заблокирован</span>
                        <?php else: ?>
                            <span class="badge-active">Активен</span>
                        <?php endif ?>
                    </td>
                    <td>
                        <a href="<?= Url::to(['/admin/user-view', 'id' => $user->id]) ?>" class="btn btn-sm btn-outline-primary">Просмотр</a>
                        <?php if ($user->role_id !== User::ROLE_ADMIN): ?>
                        <a href="<?= Url::to(['/admin/user-block', 'id' => $user->id]) ?>"
                           class="btn btn-sm <?= $user->isBlocked ? 'btn-outline-success' : 'btn-outline-danger' ?> ms-1"
                           onclick="return confirm('<?= $user->isBlocked ? 'Разблокировать?' : 'Заблокировать?' ?>')">
                            <?= $user->isBlocked ? 'Разблок.' : 'Блок.' ?>
                        </a>
                        <?php endif ?>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $dataProvider->getPagination(), 'options' => ['class' => 'pagination justify-content-center mt-3']]) ?>
</div>
