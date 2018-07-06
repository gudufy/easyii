<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\easyii\models\User;
use yii\easyii\modules\rbac\components\Helper;

$this->title = Yii::t('easyii', 'Users');
?>

<?= $this->render('_menu') ?>

<?php if($data->count > 0) : ?>
    <table class="table table-hover">
        <thead>
        <tr>
            <th width="50">#</th>
            <th><?= Yii::t('easyii', 'Mobile') ?></th>
            <th><?= Yii::t('easyii', 'Company') ?></th>
            <th><?= Yii::t('easyii', 'Full Name') ?></th>
            <th><?= Yii::t('easyii', 'Email') ?></th>
            <th width="100"><?= Yii::t('easyii', 'Status') ?></th>
            <?php if(Helper::checkRoute('/admin/users/*')) : ?>
            <th width="30"></th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data->models as $user) : ?>
            <tr>
                <td><?= $user->user_id ?></td>
                <td><a href="<?= Url::to(['/admin/users/'.(Helper::checkRoute('/admin/users/*') ? 'edit' : 'view'), 'id' => $user->user_id]) ?>"><?= $user->mobile ?></a> <?= $user->getLevelText() ?></td>
                <td><?= $user->company ?></td>
                <td><?= $user->name ?> <?= $user->getSexText() ?></td>
                <td><?= $user->email ?></td>
                <td class="status vtop">
                    <?= Html::checkbox('', $user->status == User::STATUS_ON, [
                        'class' => 'switch',
                        'data-id' => $user->primaryKey,
                        'data-link' => Url::to(['/admin/users/']),
                    ]) ?>
                </td>
                <?php if(Helper::checkRoute('/admin/users/*')) : ?>
                <td><a href="<?= Url::to(['/admin/users/delete', 'id' => $user->user_id]) ?>" class="glyphicon glyphicon-remove confirm-delete" title="<?= Yii::t('easyii', 'Delete item') ?>"></a></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <?= yii\widgets\LinkPager::widget([
            'pagination' => $data->pagination
        ]) ?>
    </table>
<?php else : ?>
    <p><?= Yii::t('easyii', 'No records found') ?></p>
<?php endif; ?>