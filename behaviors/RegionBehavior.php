<?php
namespace yii\easyii\behaviors;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\easyii\models\Region;

class RegionBehavior extends Behavior
{
    /**
     * @var string 省份字段名
     */
    public $provinceAttribute='province_id';
    /**
     * @var string 城市字段名
     */
    public $cityAttribute='city_id';
    /**
     * @var string 县字段名
     */
    public $districtAttribute='district_id';

    public function getProvince()
    {
        return $this->owner->hasOne(Region::className(),['id'=>$this->provinceAttribute]);
    }
    public function getCity()
    {
        return $this->owner->hasOne(Region::className(),['id'=>$this->cityAttribute]);
    }
    public function getDistrict()
    {
        return $this->owner->hasOne(Region::className(),['id'=>$this->districtAttribute]);
    }
    /**
     * 返回完整的地区名称
     * @example 广东深圳市宝安区
     * @param bool $useDistrict 是否要返回县/区
     * @return string
     */
    public function getFullRegion($useDistrict=true)
    {
        return $this->owner->province['name'].$this->owner->city['name'].($useDistrict ? $this->owner->district['name'] : '');
    }
}