<?php
namespace yii\easyii\modules\catalog\api;

use Yii;
use yii\easyii\components\API;
use yii\easyii\models\Photo;
use yii\easyii\modules\catalog\models\Item;
use yii\easyii\modules\guestbook\models\Guestbook;
use yii\helpers\Url;

class ItemObject extends \yii\easyii\components\ApiObject
{
    public $slug;
    public $image;
    public $data;
    public $category_id;
    public $available;
    public $discount;
    public $time;
    public $sub_title;

    private $_photos;
    private $_guestbooks;

    public function getTitle(){
        return LIVE_EDIT ? API::liveEdit($this->model->title, $this->editLink) : $this->model->title;
    }

    public function getDescription(){
        return LIVE_EDIT ? API::liveEdit($this->model->description, $this->editLink, 'div') : str_replace('src','data-original',$this->model->description);
    }

    public function getCat(){
        return Catalog::cats()[$this->category_id];
    }

    public function getPrice(){
        //return $this->discount ? round($this->model->price * (1 - $this->discount / 100) ) : $this->model->price;
        return $this->discount ? $this->discount : $this->model->price;
    }

    public function getOldPrice(){
        return $this->model->price;
    }

    public function getDate(){
        return Yii::$app->formatter->asDate($this->time);
    }

    public function getPhotos()
    {
        if(!$this->_photos){
            $this->_photos = [];

            foreach(Photo::find()->where(['class' => Item::className(), 'item_id' => $this->id])->sort()->all() as $model){
                $this->_photos[] = new PhotoObject($model);
            }
        }
        return $this->_photos;
    }

    public function getGuestbooks(){
        if(!$this->_guestbooks){
            $this->_guestbooks = [];

            foreach(Guestbook::findBySql('SELECT a.*,b.image FROM easyii_guestbook a left join easyii_users b on a.user_id=b.user_id where a.status=1 and a.goods_id='.$this->id)->all() as $model){
                $this->_guestbooks[] = new GuestbookObject($model);
            }
        }
        return $this->_guestbooks;
    }

    public function getEditLink(){
        return Url::to(['/admin/catalog/items/edit/', 'id' => $this->id]);
    }
}