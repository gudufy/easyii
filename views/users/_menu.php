<?php
use yii\helpers\Url;
use yii\easyii\modules\rbac\components\Helper;

$action = $this->context->action->id;
?>
<ul class="nav nav-pills">
    <li <?= ($action === 'index') ? 'class="active"' : '' ?>>
        <a href="<?= $this->context->getReturnUrl(['/admin/users']) ?>">
            <?php if($action === 'edit') : ?>
                <i class="glyphicon glyphicon-chevron-left font-12"></i>
            <?php endif; ?>
            <?= Yii::t('easyii', 'List') ?>
        </a>
    </li>
    <?php if(Helper::checkRoute('/admin/users/*')) : ?>
    <li <?= ($action==='create') ? 'class="active"' : '' ?>><a href="<?= Url::to(['/admin/users/create']) ?>"><?= Yii::t('easyii', 'Create') ?></a></li>
    <?php endif; ?>
</ul>
<br/>
