<?php
use yii\widgets\DetailView;
$this->title = Yii::t('easyii', 'Edit user');
?>
<?= $this->render('_menu') ?>

<?=
DetailView::widget([
    'model' => $model,
    'attributes' => [
        'mobile',
        'name',
        [                      // the owner name of the model
            'label' => '性别',
            'value' => $model->getSexText(),
        ],
        'email:email',
        'company',
        'address',
        'phone',
        'fax',
    ],
])
?>