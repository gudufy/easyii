<?php
namespace yii\easyii\behaviors;

use yii\base\Action;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\Html;
use yii\easyii\models\Region;

class RegionAction extends Action
{
    /**
     * @var \yii\db\ActiveRecord Region Model
     */
    public function init()
    {
        parent::init();
    }
    public function run()
    {
        $parent_id=Yii::$app->request->get('parent_id');
        if($parent_id>0){
            return Html::renderSelectOptions('district',Region::getRegion($parent_id));
        }else{
            return [];
        }
    }
}