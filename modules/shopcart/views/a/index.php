<?php
use yii\easyii\modules\shopcart\models\News;
use yii\easyii\modules\shopcart\models\Order;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('easyii/shopcart', 'Orders');

$module = $this->context->module->id;
?>

<?= $this->render('_menu') ?>

<?php if($data->count > 0) : ?>
    <table class="table table-hover">
        <thead>
            <tr>
                <th width="100">#</th>
                <th width="100"><?= Yii::t('easyii', 'Name') ?></th>
                <th width="150"><?= Yii::t('easyii', 'Phone') ?></th>
                <th><?= Yii::t('easyii/shopcart', 'Address') ?></th>
                <th width="100"><?= Yii::t('easyii/shopcart', 'Cost') ?></th>
                <th width="150"><?= Yii::t('easyii', 'Date') ?></th>
                <th width="90"><?= Yii::t('easyii/shopcart', 'Pay Status') ?></th>
                <th width="90"><?= Yii::t('easyii', 'Status') ?></th>
                <th width="120"></th>
            </tr>
        </thead>
        <tbody>
    <?php foreach($data->models as $item) : ?>
            <tr>
                <td>
                    <?= Html::a($item->primaryKey, ['/admin/shopcart/a/view', 'id' => $item->primaryKey]) ?>
                    <?php if($item->new) : ?>
                        <span class="label label-warning">NEW</span>
                    <?php endif; ?>
                </td>
                <td><?= $item->name ?> <?= $item->getSexText() ?></td>
                <td><?= $item->phone ?></td>
                <td><?= $item->getFullRegion() ?><?= $item->address ?></td>
                <td><?= $item->cost ?></td>
                <td><?= Yii::$app->formatter->asDatetime($item->time, 'short') ?></td>
                <td><?= $item->pay === 1 ? '已付款' : '-' ?></td>
                <td>
                <?php if($item->status === 3 && $item->is_refund===1) : ?>
                已退款
                <?php else : ?>
                <?= Order::statusName($item->status) ?>
                <?php endif; ?>
                </td>
                <td class="control">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="<?= Url::to(['/admin/'.$module.'/a/view', 'id' => $item->primaryKey]) ?>" class="btn btn-default" title="<?= Yii::t('easyii/shopcart', 'View') ?>"><span class="glyphicon glyphicon-eye-open"></span></a>
                        <a href="<?= Url::to(['/admin/'.$module.'/a/delete', 'id' => $item->primaryKey]) ?>" class="btn btn-default confirm-delete" title="<?= Yii::t('easyii', 'Delete item') ?>"><span class="glyphicon glyphicon-remove"></span></a>
                        <?php if($item->status === 3 && $item->pay===1 && $item->is_refund===0) : ?>
                        <a href="<?= Url::to(['/wechat/order-refund', 'order_sn' => $item->order_sn]) ?>" class="btn btn-default confirm-refund" title="同意退款"><span class="glyphicon">退</span></a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
    <?php endforeach; ?>
        </tbody>
    </table>
    <?= yii\widgets\LinkPager::widget([
        'pagination' => $data->pagination
    ]) ?>
<?php else : ?>
    <p><?= Yii::t('easyii', 'No records found') ?></p>
<?php endif; ?>