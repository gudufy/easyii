<?php
namespace yii\easyii\modules\shopcart\api;

use Yii;
use yii\easyii\modules\catalog\api\ItemObject;
use yii\easyii\modules\guestbook\models\Guestbook;

class GoodObject extends \yii\easyii\components\ApiObject
{
    public $order_id;
    public $item_id;
    public $options;
    public $discount;
    public $count;

    private $_item;

    public function getItem()
    {
        if(!$this->_item){
            $this->_item = new ItemObject($this->model->item);
        }
        return $this->_item;
    }

    public function getPrice(){
        return $this->discount ? $this->discount : $this->model->price;
    }

    public function getOld_price(){
        return $this->discount ? $this->model->price : null;
    }

    public function getCategory_id()
    {
        return $this->item->category_id;
    }

    public function getSlug()
    {
        return $this->item->slug;
    }

    public function getIsGuestbook(){
        return Guestbook::find()
        ->where(['goods_id' => $this->item->id,'user_id'=>Yii::$app->user->getId()])
        ->count() > 0;
    }
}