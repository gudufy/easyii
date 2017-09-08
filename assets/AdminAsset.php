<?php
namespace yii\easyii\assets;

class AdminAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@easyii/media';
    public $css = [
        'http://cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css',
        'http://cdn.bootcss.com/ionicons/2.0.1/css/ionicons.min.css',
        'http://cdn.bootcss.com/admin-lte/2.3.11/css/AdminLTE.min.css',
        'http://cdn.bootcss.com/admin-lte/2.3.11/css/skins/_all-skins.min.css',
        'css/admin.css',
    ];
    public $js = [
        'http://cdn.bootcss.com/admin-lte/2.3.11/js/app.min.js',
        'js/admin.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\easyii\assets\SwitcherAsset',
    ];
    public $jsOptions = array(
        'position' => \yii\web\View::POS_HEAD
    );
}
