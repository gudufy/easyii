<?php

return [
    'modules' => [
        'admin' => [
            'class' => 'yii\easyii\AdminModule',
        ],
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'admin/<controller:\w+>/<action:[\w-]+>/<id:\d+>' => 'admin/<controller>/<action>',
                'admin/<module:\w+>/<controller:\w+>/<action:[\w-]+>/<id:\d+>' => 'admin/<module>/<controller>/<action>'
            ],
         ],
        'user' => [
            'identityClass' => 'yii\easyii\models\User',
            'loginUrl' => ['user/login'],
            'enableAutoLogin' => true,
            'authTimeout' => 86400,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager', // or use 'yii\rbac\DbManager'
            "defaultRoles" => ["guest"],
        ],
        'i18n' => [
            'translations' => [
                'easyii' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@easyii/messages',
                    'fileMap' => [
                        'easyii' => 'admin.php',
                    ]
                ]
            ],
        ],
        'formatter' => [
            'sizeFormatBase' => 1000
        ],
        'ucpass' => [
            'class' => 'yii\easyii\components\Ucpaas',
            'accountSid' => 'bd6059ac74f198dbbea172222436ebec',
            'token' => 'f8d89bb1046ec3d1aaf99292d0867d31',
            'appId' => '63e29207ac85437f889be1e81a747da3',
            'templateId' => '35446',
        ],
    ],
    'as access' => [
        'class' => 'yii\easyii\modules\rbac\components\AccessControl',
        'allowActions' => [
            'admin/sign/in',
        ]
    ],
    'bootstrap' => ['admin']
];