<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $statusFilter */
/** @var string $search */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Airline;

$this->title = 'Авиакомпании';

$statusBadges = [
    Airline::STATUS_PENDING => ['badge-pending', 'На проверке'],
    Airline::STATUS_ACTIVE  => ['badge-active',  'Активна'],
    Airline::STATUS_BLOCKED => ['badge-blocked',  'Заблокирована'],
];
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Авиакомпании</h4>
        <a href="<?= Url::to(['/admin/index']) ?>" class="btn btn-outline-secondary btn-sm">← Панель</a>
    </div>

    <?= \app\widgets\Alert::widget() ?>

    <form method="get" action="<?= Url::to(['/admin/airlines']) ?>" class="mb-3 d-flex gap-2 align-items-center flex-wrap">
        <input type="text" name="search" class="form-control form-control-sm" style="max-width:240px"
               placeholder="Название или email" value="<?= Html::encode($search) ?>">
        <select name="status" class="form-select form-select-sm" style="max-width:170px">
            <option value="">Все статусы</option>
            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>⏳ На проверке</option>
            <option value="active"  <?= $statusFilter === 'active'  ? 'selected' : '' ?>>Активные</option>
            <option value="blocked" <?= $statusFilter === 'blocked' ? 'selected' : '' ?>>Заблокированные</option>
        </select>
        <button type="submit" class="btn btn-sm btn-primary">Найти</button>
        <?php if ($search !== '' || $statusFilter !== ''): ?>
        <a href="<?= Url::to(['/admin/airlines']) ?>" class="btn btn-sm btn-outline-secondary">↺ Сбросить</a>
        <?php endif ?>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Страна</th>
                    <th>Владелец</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $airline): ?>
                <?php [$badgeClass, $statusText] = $statusBadges[$airline->airline_status_id] ?? ['badge-wait', '?']; ?>
                <tr>
                    <td><?= $airline->id ?></td>
                    <td class="fw-semibold"><?= Html::encode($airline->name) ?></td>
                    <td><?= Html::encode($airline->country) ?></td>
                    <td class="small"><?= $airline->ownerUser ? Html::encode($airline->ownerUser->getFullName()) : '—' ?></td>
                    <td><span class="<?= $badgeClass ?>"><?= $statusText ?></span></td>
                    <td class="small text-muted"><?= date('d.m.Y', strtotime($airline->created_at)) ?></td>
                    <td>
                        <a href="<?= Url::to(['/catalog/airline', 'id' => $airline->id]) ?>" class="btn btn-sm btn-outline-primary">Просмотр</a>
                        <?php if ($airline->isPending): ?>
                        <a href="<?= Url::to(['/admin/airline-approve', 'id' => $airline->id]) ?>"
                           class="btn btn-sm btn-success ms-1"
                           onclick="return confirm('Подтвердить авиакомпанию?')">✓ Одобрить</a>
                        <?php endif ?>
                        <?php if (!$airline->isPending): ?>
                        <a href="<?= Url::to(['/admin/airline-block', 'id' => $airline->id]) ?>"
                           class="btn btn-sm <?= $airline->isBlocked ? 'btn-outline-success' : 'btn-outline-danger' ?> ms-1"
                           onclick="return confirm('<?= $airline->isBlocked ? 'Разблокировать?' : 'Заблокировать?' ?>')">
                            <?= $airline->isBlocked ? 'Разблок.' : 'Блок.' ?>
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
