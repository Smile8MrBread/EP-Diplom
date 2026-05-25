<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\User;

$this->title = Html::encode($user->getFullName());
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= Url::to(['/admin/index']) ?>">Панель</a></li>
            <li class="breadcrumb-item"><a href="<?= Url::to(['/admin/users']) ?>">Пользователи</a></li>
            <li class="breadcrumb-item active"><?= Html::encode($user->getFullName()) ?></li>
        </ol>
    </nav>

    <?= \app\widgets\Alert::widget() ?>

    <div class="row">
        <div class="col-md-7">
            <div class="card p-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="profile-avatar"><?= mb_strtoupper(mb_substr($user->first_name, 0, 1)) ?></div>
                    <div>
                        <h5 class="fw-bold mb-0"><?= Html::encode($user->getFullName()) ?></h5>
                        <span class="text-muted small"><?= Html::encode($user->role->role) ?></span>
                    </div>
                </div>
                <dl class="row small">
                    <dt class="col-4 text-muted">Email</dt><dd class="col-8"><?= Html::encode($user->email) ?></dd>
                    <dt class="col-4 text-muted">Телефон</dt><dd class="col-8"><?= Html::encode($user->phone) ?></dd>
                    <dt class="col-4 text-muted">Дата рождения</dt><dd class="col-8"><?= $user->birth_date ? date('d.m.Y', strtotime($user->birth_date)) : '—' ?></dd>
                    <dt class="col-4 text-muted">Статус</dt>
                    <dd class="col-8">
                        <?php if ($user->isBlocked): ?>
                            <span class="badge-blocked">Заблокирован</span>
                        <?php else: ?>
                            <span class="badge-active">Активен</span>
                        <?php endif ?>
                    </dd>
                </dl>
            </div>

            <!-- Orders -->
            <h6 class="fw-bold mb-2">Заказы (<?= count($user->orders) ?>)</h6>
            <?php if ($user->orders): ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>#</th><th>Сумма</th><th>Статус</th><th>Дата</th></tr></thead>
                    <tbody>
                        <?php foreach (array_slice($user->orders, 0, 10) as $order): ?>
                        <tr>
                            <td><?= $order->id ?></td>
                            <td><?= $order->getFormattedTotal() ?></td>
                            <td><?= Html::encode($order->orderStatus->status) ?></td>
                            <td class="small"><?= date('d.m.Y', strtotime($order->date ?? 'now')) ?></td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="text-muted small">Заказов нет.</p>
            <?php endif ?>
        </div>

        <div class="col-md-5">
            <div class="card p-4">
                <h6 class="fw-bold mb-3">Действия</h6>
                <?php if ($user->role_id !== User::ROLE_ADMIN): ?>
                <a href="<?= Url::to(['/admin/user-block', 'id' => $user->id]) ?>"
                   class="btn <?= $user->isBlocked ? 'btn-outline-success' : 'btn-outline-danger' ?> w-100"
                   onclick="return confirm('<?= $user->isBlocked ? 'Разблокировать?' : 'Заблокировать?' ?>')">
                    <?= $user->isBlocked ? 'Разблокировать' : 'Заблокировать' ?>
                </a>
                <?php else: ?>
                    <p class="text-muted small">Нельзя заблокировать администратора.</p>
                <?php endif ?>
                <a href="<?= Url::to(['/admin/users']) ?>" class="btn btn-outline-secondary w-100 mt-2">← К списку</a>
            </div>
        </div>
    </div>
</div>
