<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \yii\easyii\modules\rbac\models\form\ChangePassword */

$this->title = '修改密码';
//$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-change',
    ]); ?>
<?= $form->field($model, 'oldPassword')->passwordInput() ?>
<?= $form->field($model, 'newPassword')->passwordInput() ?>
<?= $form->field($model, 'retypePassword')->passwordInput() ?>
<div class="form-group">
    <?= Html::submitButton('修 改', ['class' => 'btn btn-primary', 'name' => 'change-button']) ?>
</div>
<?php ActiveForm::end(); ?>