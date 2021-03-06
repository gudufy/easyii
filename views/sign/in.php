<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\easyii\models\Setting;

$asset = \yii\easyii\assets\EmptyAsset::register($this);
$this->title = Yii::t('easyii', 'Sign in');
?>
<div class="container">
    <div id="wrapper" class="col-md-4 col-md-offset-4 vertical-align-parent">
        <div class="vertical-align-child">
            <div class="panel">
                <div class="panel-heading text-center">
                    <?= Yii::t('easyii', 'Sign in') ?>
                </div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin([
                        'fieldConfig' => [
                            'template' => "{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}"
                        ]
                    ])
                    ?>
                        <?= $form->field($model, 'username')->textInput(['class'=>'form-control', 'placeholder'=>Yii::t('easyii', 'Username')]) ?>
                        <?= $form->field($model, 'password')->passwordInput(['class'=>'form-control', 'placeholder'=>Yii::t('easyii', 'Password')]) ?>
                        <!--<p class="text-right">
                        <?= Html::a('Reset it', ['user/request-password-reset']) ?>
                        </p>-->
                        <?=Html::submitButton(Yii::t('easyii', 'Login'), ['class'=>'btn btn-lg btn-primary btn-block']) ?>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <div class="text-center">
                <a class="logo" href="/" title="<?=Setting::get('site_name') ?> homepage">
                    <img src="<?= $asset->baseUrl ?>/img/logo_20.png"><?=Setting::get('site_name') ?>
                </a>
            </div>
        </div>
    </div>
</div>
