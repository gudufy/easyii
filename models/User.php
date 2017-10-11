<?php
namespace yii\easyii\models;

use Yii;

class User extends \yii\easyii\components\ActiveRecord implements \yii\web\IdentityInterface
{
    public $total;
    
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
            [['username','name','sex'], 'required'],
            ['mobile','match','pattern'=>'/^1[0-9]{10}$/','message'=>'{attribute}必须为1开头的11位纯数字'],
            ['username', 'unique'],
            ['password_hash', 'required', 'on' => 'create'],
            ['password_hash', 'safe'],
            ['image', 'image'],
            ['access_token', 'default', 'value' => null],
            [['company','address','phone','fax','openid','access_token','refresh_token'],'string'],
            [['created_at'],'integer'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'email'],

        ];
    }

    public function attributeLabels()
    {
        return [
            'image' => Yii::t('easyii', 'Image'),
            'username' => Yii::t('easyii', 'Username'),
            'password_hash' => Yii::t('easyii', 'Password'),
            'name' => Yii::t('easyii', 'Full Name'),
            'mobile' => Yii::t('easyii', 'Mobile'),
            'sex' => Yii::t('easyii', 'Gender'),
            'company' => Yii::t('easyii', 'Company'),
            'address' => Yii::t('easyii', 'Address'),
            'phone' => Yii::t('easyii', 'Phone'),
            'fax' => Yii::t('easyii', 'Fax'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                if ($this->username == 'root'){
                    $this->addError('username', Yii::t('easyii', '该用户名已经被使用.'));
                    return false;
                }

                $this->username = $this->username ? $this->username:  $this->mobile;

                if (static::findByMobile($this->mobile) !== null){
                    $this->addError('mobile', Yii::t('easyii', '该手机号已经被使用.'));
                    return false;
                }

                if (static::findByUsername($this->username) !== null){
                    $this->addError('username', Yii::t('easyii', '该用户名已经被使用.'));
                    return false;
                }

                $this->auth_key = $this->generateAuthKey();
                $this->password_hash = $this->hashPassword($this->password_hash);
                $this->created_at = time();
            } else {
                if ($this->email != $this->oldAttributes['email'] && static::findByEmail($this->email) !== null){
                    $this->addError('email', Yii::t('easyii', '该邮箱已经被使用.'));
                    return false;
                }

                if ($this->mobile != $this->oldAttributes['mobile'] && static::findByMobile($this->mobile) !== null){
                    $this->addError('mobile', Yii::t('easyii', '该手机已经被使用.'));
                    return false;
                }
                
                $this->password_hash = $this->password_hash && $this->password_hash != '' ? $this->hashPassword($this->password_hash) : $this->oldAttributes['password_hash'];
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

    public static function findByMobile($mobile)
    {
        return static::findOne(['mobile' => $mobile]);
    }

    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
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
        if ($this->username === 'root')
            return $this->password_hash === $this->hashPassword($password);
            
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    private function hashPassword($password)
    {
        if ($this->username==='root')
            return sha1($password . $this->getAuthKey() . Setting::get('password_salt'));
            
        return Yii::$app->security->generatePasswordHash($password);
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

    /**
    * Finds all users by assignment role
    *
    * @param  \yii\rbac\Role $role
    * @return static|null
    */
    public static function findByRole($role_name)
    {
        return static::find()
            ->join('LEFT JOIN','auth_assignment','auth_assignment.user_id = easyii_users.user_id')
            ->where(['auth_assignment.item_name' => $role_name]);
    }
}
