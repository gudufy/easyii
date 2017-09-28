<?php

namespace yii\easyii\behaviors;

use Yii;
use yii\base\Action;
use yii\easyii\helpers\SMS;
use yii\easyii\models\User;
use yii\easyii\models\SMSCode;

class SMSCodeAction extends Action {
    public function run($validator = false) {
        Yii::$app->getResponse()->format = 'json';
        $result = [];

        $mobile = Yii::$app->request->getQueryParam('mobile');

        if ($validator != null){
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
        }

        $sendTotal = SMSCode::find()->where(['mobile'=>$mobile,"FROM_UNIXTIME(created_at,'%Y-%m-%d')"=>'CURDATE()'])->count();
        
        if ($sendTotal < 8){
            if (SMS::sendSms($mobile)) {
                $result['success'] = true;
            } else {
                $result['success'] = false;
                $result['msg'] = '验证码发送失败';
            }
        }
        else{
            $result['success'] = false;
            $result['msg'] = '您今天发送的次数过多，请换个手机号或明天再试！';
        }

        return $result;
    }
}