<?php
namespace yii\easyii\models;

use Yii;

class User extends \yii\easyii\components\ActiveRecord implements \yii\web\IdentityInterface
{
    const STATUS_OFF = 0;
    const STATUS_ON = 1;
    
    static $rootUser = [
        'user_id' => 0,
        'username' => 'root',
        'password_hash' => '',
        'auth_key' => '',
        'access_token' => '',
        'status' => 0
    ];

    public static function tableName()
    {
        return 'easyii_users';
    }

    public function rules()
    {
        return [
            [['username','mobile','name','sex'], 'required'],
            ['username', 'unique'],
            ['password_hash', 'required', 'on' => 'create'],
            ['password_hash', 'safe'],
            ['image', 'image'],
            ['access_token', 'default', 'value' => null]
        ];
    }

    public function attributeLabels()
    {
        return [
            'image' => Yii::t('easyii', 'Image'),
            'username' => Yii::t('easyii', 'Username'),
            'password' => Yii::t('easyii', 'Password'),
            'name' => Yii::t('easyii', 'Full Name'),
            'mobile' => Yii::t('easyii', 'Mobile'),
            'sex' => Yii::t('easyii', 'Gender'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                if ($this->username == 'root'){
                    $this->addError('username', Yii::t('easyii', 'The username cannot be used.'));
                    return false;
                }

                $this->auth_key = $this->generateAuthKey();
                $this->password = $this->hashPassword($this->password);
            } else {
                $this->password = $this->password != '' ? $this->hashPassword($this->password) : $this->oldAttributes['password'];
            }
            return true;
        } else {
            return false;
        }
    }

    public function getSexs (){
        return [0 => Yii::t('easyii', 'Man'), 1 => Yii::t('easyii', 'Woman')];
    }

    public function getSexText(){
        return $this->getSexs()[$this->sex];
    }

    public static function findIdentity($id)
    {
        $result = null;
        try {
            $result = $id == self::$rootUser['user_id']
                ? static::createRootUser()
                : static::findOne($id);
        } catch (\yii\base\InvalidConfigException $e) {
        }
        return $result;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public static function findByUsername($username)
    {
        if ($username === self::$rootUser['username']) {
            return static::createRootUser();
        }
        return static::findOne(['username' => $username]);
    }

    public function getId()
    {
        return $this->user_id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return $this->password_hash === $this->hashPassword($password);
    }

    private function hashPassword($password)
    {
        return sha1($password . $this->getAuthKey() . Setting::get('password_salt'));
    }

    private function generateAuthKey()
    {
        return Yii::$app->security->generateRandomString();
    }

    public static function createRootUser()
    {
        return new static(array_merge(self::$rootUser, [
            'password_hash' => Setting::get('root_password'),
            'auth_key' => Setting::get('root_auth_key')
        ]));
    }

    public function isRoot()
    {
        return $this->username === self::$rootUser['username'];
    }
}
