<?php
use yii\helpers\Html;

$this->title = $subject;
?>
<p>用户 <b><?= $feedback->name ?></b> 提交了留言。</p>
<p>你可以点这里进行查看 <?= Html::a('here', $link) ?>.</p>