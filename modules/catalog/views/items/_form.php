<?php
use yii\easyii\helpers\Image;
use yii\easyii\widgets\DateTimePicker;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\easyii\widgets\Redactor;
use yii\easyii\widgets\SeoForm;

$settings = $this->context->module->settings;
$module = $this->context->module->id;
?>

<?php $form = ActiveForm::begin([
    'options' => ['enctype' => 'multipart/form-data', 'class' => 'model-form']
]); ?>
<div class="row">
    <div class="col-md-8">

<?= $form->field($model, 'title') ?>
<?= $form->field($model, 'sub_title') ?>
<?php if($settings['itemThumb']) : ?>
    <?php if($model->image) : ?>
        <img src="<?= Image::thumb($model->image, 240) ?>">
        <a href="<?= Url::to(['/admin/'.$module.'/items/clear-image', 'id' => $model->primaryKey]) ?>" class="text-danger confirm-delete" title="<?= Yii::t('easyii', 'Clear image')?>"><?= Yii::t('easyii', 'Clear image')?></a>
    <?php endif; ?>
    <?= $form->field($model, 'image')->fileInput() ?>
<?php endif; ?>
<?= $dataForm ?>
    <?= $form->field($model, 'description')->widget(Redactor::className(),[
        'options' => [
            'minHeight' => 400,
            'imageUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'catalog'], true),
            'fileUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'catalog'], true),
            'plugins' => ['fullscreen']
        ]
    ]) ?>

    </div>
    <div class="col-md-4">
<?= $form->field($model, 'slug') ?>
<?= $form->field($model, 'recommended')->checkbox() ?>
<?php if($settings['itemSale']) : ?>
<?= $form->field($model, 'available')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'discount')->textInput(['maxlength' => true]) ?>
<?php endif; ?>
<?= $form->field($model, 'time')->widget(DateTimePicker::className()); ?>

<?= SeoForm::widget(['model' => $model]) ?>

<?= Html::submitButton(Yii::t('easyii', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>
</div>



<?php ActiveForm::end(); ?>