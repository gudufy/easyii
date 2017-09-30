<?php

namespace yii\easyii\models;

use Yii;
use yii\base\Model;
use yii\easyii\models\Setting;

/**
 * Description of ChangeRootPassword
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class ChangeRootPassword extends Model
{
    public $newPassword;
    public $retypePassword;
    public $oldPassword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['oldPassword', 'newPassword', 'retypePassword'], 'required'],
            [['oldPassword'], 'validatePassword'],
            [['newPassword'], 'string', 'min' => 6],
            [['retypePassword'], 'compare', 'compareAttribute' => 'newPassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'oldPassword'=>'旧密码',
            'newPassword'=>'新密码',
            'retypePassword' => '确认密码',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
     public function validatePassword()
     {
        $password_salt = Setting::get('password_salt');
        $root_auth_key = Setting::get('root_auth_key');
        $old_root_password = Setting::get('root_password');
        $root_password = sha1($this->oldPassword.$root_auth_key.$password_salt);
         if ($old_root_password !== $root_password) {
             $this->addError('oldPassword', 'Incorrect old password.');
         }
     }

    /**
     * Change password.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function change()
    {
        if ($this->validate()) {
            /* @var $user User */
            $password_salt = Yii::$app->security->generateRandomString();
            $root_auth_key = Yii::$app->security->generateRandomString();
            $root_password = sha1($this->newPassword.$root_auth_key.$password_salt);

            Setting::set('root_password',$root_password);
            Setting::set('root_auth_key',$root_auth_key);
            Setting::set('password_salt',$password_salt);

            return true;
        }

        return false;
    }

    public function formatErrors()
    {
        $result = '';
        foreach($this->getErrors() as $attribute => $errors) {
            $result .= implode(" ", $errors)."\n";
        }
        return $result;
    }
}
