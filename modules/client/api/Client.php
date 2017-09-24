<?php
namespace yii\easyii\modules\client\api;

use Yii;
use yii\easyii\components\API;
use yii\easyii\helpers\Data;
use yii\easyii\modules\client\models\Client as ClientModel;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Client module API
 * @package yii\easyii\modules\Client\api
 * @method static array items() array of all Client items as ClientObject objects. Useful to create Client on other widgets.
 */

class Client extends API
{
    public $clientOptions = ['interval' => 5000];

    private $_items = [];

    public function init()
    {
        parent::init();

        $this->_items = Data::cache(ClientModel::CACHE_KEY, 3600, function(){
            $items = [];
            foreach(ClientModel::find()->status(ClientModel::STATUS_ON)->sort()->all() as $item){
                $items[] = new ClientObject($item);
            }
            return $items;
        });
    }

    public function api_items()
    {
        return $this->_items;
    }
}