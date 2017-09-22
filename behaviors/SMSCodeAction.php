<?php

namespace yii\easyii\behaviors;

use Yii;
use yii\base\Action;
use yii\easyii\helpers\SMS;
use yii\easyii\models\User;

class SMSCodeAction extends Action {
    public function run($validator = false) {
        Yii::$app->getResponse()->format = 'json';
        $result = [];

        $mobile = Yii::$app->request->getQueryParam('mobile');

        if($validator && !User::findByMobile($mobile)){
            $result['success'] = false;
            $result['msg'] = '该手机号还未注册。';

            return $result;
        }

        if(!$validator && User::findByMobile($mobile)){
            $result['success'] = false;
            $result['msg'] = '该手机号已被注册。';

            return $result;
        }
        
        if (SMS::sendSms($mobile)) {
            $result['success'] = true;
        } else {
            $result['success'] = false;
            $result['msg'] = '验证码发送失败';
        }

        return $result;
    }
}