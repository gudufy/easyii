<?php
use yii\easyii\helpers\Image;
use yii\easyii\widgets\DateTimePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\easyii\widgets\Redactor;
use yii\easyii\widgets\SeoForm;

use yii\easyii\modules\catalog\models\Category;

$settings = $this->context->module->settings;
$module = $this->context->module->id;

$cats = ArrayHelper::map(Category::find()->all(),'category_id','title');
?>

<?php \app\widgets\JsBlock::begin() ?>
<script type="text/javascript">
    
</script>
<?php \app\widgets\JsBlock::end()?>

<?php $form = ActiveForm::begin([
    'options' => ['enctype' => 'multipart/form-data', 'class' => 'model-form']
]); ?>
<div class="row">
    <div class="col-md-8">

<?= $form->field($model, 'title') ?>
<?= $form->field($model, 'sub_title') ?>
<?= $form->field($model, 'category_id')->dropDownList(
                                $cats, 
                                ['prompt'=>Yii::t('easyii','Please select...')]) ?>
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
<div id='old-prices'>
<?= $form->field($model, 'available')->textInput(['maxlength' => true]) ?>
<!-- <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?> -->
<?= $form->field($model, 'discount')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'price1')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'price2')->textInput(['maxlength' => true]) ?>
        </div>
<?php endif; ?>

<?= $form->field($model, 'time')->widget(DateTimePicker::className()); ?>

<?= SeoForm::widget(['model' => $model]) ?>

<?= Html::submitButton(Yii::t('easyii', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>
</div>



<?php ActiveForm::end(); ?>