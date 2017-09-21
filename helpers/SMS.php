<?php
namespace yii\easyii\helpers;

use Yii;
use yii\easyii\models\SMSCode;
use yii\easyii\components\Ucpaas;

class SMS
{
    /**
     * 发送短信验证码
     * @param bool $runValidation
     * @return bool
     * @throws ServerErrorHttpException
     */
     public static function sendSms($mobile) {
        if (!$mobile) {
            return false;
        }
        $verifyCode = (string) mt_rand(100000, 999999);
        $validMinutes = 30;
        
        // 调用云之讯组件发送模板短信
        /** @var $ucpass Ucpaas */
        $ucpass = Yii::$app->ucpass;
        $ucpass->templateSMS($mobile, $verifyCode.','.$validMinutes);
        if ($ucpass->state == Ucpaas::STATUS_SUCCESS) {
            $model = new SMSCode();
            $model->mobile = $mobile;
            $model->code = $verifyCode;
            $model->action_sign = 'register';
            $model->valid_second = $validMinutes * 60;
            $model->used= 0;
            $model->created_at = time();
            $model->save();

            return true;
        } else {
            return false;
        }
    }
}