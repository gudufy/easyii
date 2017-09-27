<?php
namespace yii\easyii\helpers;

use yii\validators\Validator;
use yii\easyii\models\SMSCode;

class SMSValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        // 获取参数
        $mobile = $model->mobile;
        $code = $model->smscode;
        if (empty($mobile)) {
            $this->addError($model,$attribute,'请先输入手机号');
        } else if (empty($code)) {
            $this->addError($model,$attribute,'请输入您收到的验证码');
        }
        // 获取验证码的信息
        $sms = SMSCode::findOne([
            'mobile' => $mobile,
            'action_sign' => 'register',
            'code' => $code,
            'used' => 0
        ]);
        if ($sms === null) {
            $this->addError($model,$attribute,'验证码输入错误,请重新输入');
        }
        else{
            // 判断是否失效
            if (time() - $sms['created_at'] > $sms['valid_second']) {
                $this->addError($model,$attribute,'您的验证码已过期,请重新获取');
            }
            else{
                $sms->used = 1;
                $sms->save();
            }
        }
        
    }
}