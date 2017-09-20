<?php
    use yii\helpers\Html;
    use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin([
    'enableClientValidation' => true,
    'options' => ['enctype' => 'multipart/form-data', 'class' => 'model-form']
]); ?>
<?php if($model->image) : ?>
    <img src="<?= $model->image ?>" style="height:200px;">
<?php endif; ?>
<?= $form->field($model, 'image')->fileInput() ?>
<?= $form->field($model, 'username')->textInput($this->context->action->id === 'edit' ? ['disabled' => 'disabled'] : []) ?>
<?= $form->field($model, 'password_hash')->passwordInput(['value' => '']) ?>
<?= $form->field($model, 'name')->textInput() ?>
<?= $form->field($model, 'mobile')->textInput() ?>
<?= $form->field($model, 'sex')->radioList($model->getSexs()) ?>
<?= Html::submitButton(Yii::t('easyii', 'Save'), ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>