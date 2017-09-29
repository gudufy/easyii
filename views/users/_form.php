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
<?= $form->field($model, 'mobile')->textInput() ?>
<?= $form->field($model, 'password_hash')->passwordInput(['value' => '']) ?>
<?= $form->field($model, 'name')->textInput() ?>
<?= $form->field($model, 'email')->textInput() ?>
<?= $form->field($model, 'sex')->radioList($model->getSexs()) ?>
<?= $form->field($model, 'company')->textInput() ?>
<?= $form->field($model, 'address')->textInput() ?>
<?= $form->field($model, 'phone')->textInput() ?>
<?= $form->field($model, 'fax')->textInput() ?>
<?= Html::submitButton(Yii::t('easyii', 'Save'), ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>