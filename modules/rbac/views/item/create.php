<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model yii\easyii\modules\rbac\models\AuthItem */
/* @var $context yii\easyii\modules\rbac\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$this->title = Yii::t('rbac-admin', 'Create ' . $labels['Item']);
$this->params['breadcrumbs'][] = ['label' => Yii::t('rbac-admin', $labels['Items']), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-item-create">
    <?=
    $this->render('_form', [
        'model' => $model,
    ]);
    ?>

</div>
