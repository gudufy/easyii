<?php
namespace yii\easyii\modules\catalog\models;

use Yii;
use yii\easyii\behaviors\SluggableBehavior;
use yii\easyii\behaviors\SeoBehavior;
use yii\easyii\models\Photo;

class Item extends \yii\easyii\components\ActiveRecord
{
    const STATUS_OFF = 0;
    const STATUS_ON = 1;

    public static function tableName()
    {
        return 'easyii_catalog_items';
    }

    public function rules()
    {
        return [
            ['title', 'required'],
            [['title','sub_title'], 'trim'],
            [['title','sub_title'], 'string', 'max' => 128],
            ['image', 'image'],
            ['description', 'safe'],
            ['price', 'number'],
            [['discount','price1','price2'], 'double'],
            [['status', 'category_id', 'available', 'time','recommended'], 'integer'],
            ['time', 'default', 'value' => time()],
            ['slug', 'match', 'pattern' => self::$SLUG_PATTERN, 'message' => Yii::t('easyii', 'Slug can contain only 0-9, a-z and "-" characters (max: 128).')],
            ['slug', 'default', 'value' => null],
            ['status', 'default', 'value' => self::STATUS_ON],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => Yii::t('easyii', 'Title'),
            'sub_title' => Yii::t('easyii/catalog', 'Sub Title'),
            'image' => Yii::t('easyii', 'Image'),
            'description' => Yii::t('easyii', 'Description'),
            'available' => Yii::t('easyii/catalog', 'Available'),
            'recommended' => Yii::t('easyii/catalog', 'Recommended'),
            'price' => Yii::t('easyii/catalog', 'Price'),
            'discount' => Yii::t('easyii/catalog', 'Discount'),
            'price1' => Yii::t('easyii/catalog', 'Price1'),
            'price2' => Yii::t('easyii/catalog', 'Price2'),
            'time' => Yii::t('easyii', 'Date'),
            'slug' => Yii::t('easyii', 'Slug'),
        ];
    }

    public function behaviors()
    {
        return [
            'seoBehavior' => SeoBehavior::className(),
            'sluggable' => [
                'class' => SluggableBehavior::className(),
                'attribute' => 'title',
                'ensureUnique' => true
            ]
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if(!$this->data || (!is_object($this->data) && !is_array($this->data))){
                $this->data = new \stdClass();
            }

            $this->data = json_encode($this->data);

            if(!$insert && $this->image != $this->oldAttributes['image'] && $this->oldAttributes['image']){
                //@unlink(Yii::getAlias('@webroot').$this->oldAttributes['image']);
            }

            return true;
        } else {
            return false;
        }
    }

    public function afterSave($insert, $attributes){
        parent::afterSave($insert, $attributes);

        $this->parseData();

        ItemData::deleteAll(['item_id' => $this->primaryKey]);

        foreach($this->data as $name => $value){
            if(!is_array($value)){
                $this->insertDataValue($name, $value);
            } else {
                foreach($value as $arrayItem){
                    $this->insertDataValue($name, $arrayItem);
                }
            }
        }
    }

    private function insertDataValue($name, $value){
        Yii::$app->db->createCommand()->insert(ItemData::tableName(), [
            'item_id' => $this->primaryKey,
            'name' => $name,
            'value' => $value
        ])->execute();
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->parseData();
    }

    public function getPhotos()
    {
        return $this->hasMany(Photo::className(), ['item_id' => 'item_id'])->where(['class' => self::className()])->sort();
    }

    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['category_id' => 'category_id']);
    }

    public function getPrice(){
        if(Yii::$app->getUser()->isGuest){
            return $this->discount;
        }
        else{
            $user = Yii::$app->user->getIdentity();
            switch ($user->level) {
                case 1:
                    return $this->price1;
                case 2:
                    return $this->price2;
                default:
                    return $this->discount;
            }
        }

        //return $this->discount ? $this->discount : $this->model->price;
    }

    public function afterDelete()
    {
        parent::afterDelete();

        foreach($this->getPhotos()->all() as $photo){
            $photo->delete();
        }

        if($this->image) {
            @unlink(Yii::getAlias('@webroot') . $this->image);
        }

        ItemData::deleteAll(['item_id' => $this->primaryKey]);
    }

    private function parseData(){
        $this->data = $this->data !== '' ? json_decode($this->data) : [];
    }
}