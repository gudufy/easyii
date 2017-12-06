<?php

namespace yii\easyii\models;

use Yii;
use yii\easyii\behaviors\RegionBehavior;

/**
 * This is the model class for table "easyii_user_address".
 *
 * @property int $address_id
 * @property int $user_id
 * @property string $country
 * @property string $province
 * @property string $city
 * @property string $district
 * @property string $name
 * @property int $sex
 * @property string $mobile
 * @property string $phone
 * @property string $address
 * @property int $created_at
 * @property int $updated_at
 */
class UserAddress extends \yii\easyii\components\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'easyii_user_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_id', 'province_id', 'city_id', 'district_id', 'user_id', 'sex', 'created_at', 'updated_at','is_default'], 'integer'],
            [['name', 'province_id', 'city_id', 'district_id', 'address','mobile'], 'required'],
            [[ 'address'], 'string', 'max' => 45],
            [['name', 'phone'], 'string', 'max' => 32],
            [['mobile'], 'string', 'max' => 11],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'address_id' => Yii::t('easyii', 'Address ID'),
            'user_id' => Yii::t('easyii', 'User ID'),
            'province' => Yii::t('easyii', 'Area'),
            'country_id' => Yii::t('easyii', 'Country'),
            'province_id' => Yii::t('easyii', 'Province'),
            'city_id' => Yii::t('easyii', 'City'),
            'district_id' => Yii::t('easyii', 'District'),
            'is_default' => Yii::t('easyii', 'Default'),
            'name' => Yii::t('easyii', 'Full Name'),
            'sex' => Yii::t('easyii', 'Gender'),
            'mobile' => Yii::t('easyii', 'Mobile'),
            'phone' => Yii::t('easyii', 'Phone'),
            'address' => Yii::t('easyii', 'Address'),
            'created_at' => Yii::t('easyii', 'Created At'),
            'updated_at' => Yii::t('easyii', 'Updated At'),
        ];
    }

    public function behaviors()
    {
        return [
            RegionBehavior::className()
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->is_default){
                static::updateAll(['is_default' => 0],['=', 'user_id', Yii::$app->user->getId()]);
            }

            if ($this->isNewRecord) {
                $this->user_id = Yii::$app->user->getId();
                $this->created_at = time();
            } else {
                $this->updated_at =time();
            }
            return true;
        } else {
            return false;
        }
    }

    public static function findByMobile($mobile)
    {
        return static::findOne(['mobile' => $mobile,'user_id'=>Yii::$app->user->getId()]);
    }

    public static function getTotal()
    {
        return static::find()
        ->where(['user_id' => Yii::$app->user->getId()])
        ->count();
    }

    public function getSexText(){
        return \yii\easyii\models\User::getSexs()[$this->sex];
    }
}
