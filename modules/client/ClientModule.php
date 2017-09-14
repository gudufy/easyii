<?php
namespace yii\easyii\modules\Client;

class ClientModule extends \yii\easyii\components\Module
{
    public $settings = [
        'enableTitle' => true,
        'enableText' => false,
    ];

    public static $installConfig = [
        'title' => [
            'en' => 'Client',
            'ru' => 'Карусель',
        ],
        'icon' => 'user',
        'order_num' => 40,
    ];
}