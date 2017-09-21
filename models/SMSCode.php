<?php
namespace yii\easyii\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "sms_record".
 *
 * @property integer $id
 * @property string $mobile
 * @property string $code
 * @property string $action_sign
 * @property integer $valid_second
 * @property integer $created_at
 */
class SMSCode extends ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%easyii_sms_code}}';
    }
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['mobile', 'code', 'valid_second', 'action_sign', 'created_at'], 'required'],
            [['valid_second', 'created_at', 'used'], 'integer'],
            [['mobile', 'action_sign'], 'string', 'max' => 20],
            [['code'], 'string', 'max' => 10]
        ];
    }
}