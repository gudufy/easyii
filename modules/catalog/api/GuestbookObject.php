<?php
namespace yii\easyii\modules\catalog\api;

use Yii;
use yii\easyii\components\API;
use yii\easyii\models\Photo;
use yii\easyii\modules\catalog\models\Item;
use yii\easyii\modules\guestbook\models\Guestbook;
use yii\helpers\Url;

class GuestbookObject extends \yii\easyii\components\ApiObject
{
    public $name;
    public $text;
    public $star_rating;
    public $time;

    public function getAvatar(){
        if ($this->model->user_id > 0)
        {
            return $this->model['image'];
        }
        return '';
    }

    public function getDateText()
    {
        return date('Y/m/d', $this->time);
    }
}