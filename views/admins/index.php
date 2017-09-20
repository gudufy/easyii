<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\easyii\models\User;

$this->title = Yii::t('easyii', 'Admins');
?>

<?= $this->render('_menu') ?>

<?php if($data->count > 0) : ?>
<table class="table table-hover">
<thead>
<tr>
    <th width="50">#</th>
    <th><?= Yii::t('easyii', 'Username') ?></th>
    <th><?= Yii::t('easyii', 'Full Name') ?></th>
    <th><?= Yii::t('easyii', 'Mobile') ?></th>
    <th><?= Yii::t('easyii', 'Gender') ?></th>
    <th width="100"><?= Yii::t('easyii', 'Status') ?></th>
    <th width="30"></th>
</tr>
</thead>
<tbody>
<?php foreach($data->models as $user) : ?>
    <tr>
        <td><?= $user->user_id ?></td>
        <td><a href="<?= Url::to(['/admin/users/edit', 'id' => $user->user_id]) ?>"><?= $user->username ?></a></td>
        <td><?= $user->name ?></td>
        <td><?= $user->mobile ?></td>
        <td><?= $user->getSexText() ?></td>
        <td class="status vtop">
            <?= Html::checkbox('', $user->status == User::STATUS_ON, [
                'class' => 'switch',
                'data-id' => $user->primaryKey,
                'data-link' => Url::to(['/admin/users/']),
            ]) ?>
        </td>
        <td><a href="<?= Url::to(['/admin/users/delete', 'id' => $user->user_id]) ?>" class="glyphicon glyphicon-remove confirm-delete" title="<?= Yii::t('easyii', 'Delete item') ?>"></a></td>
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