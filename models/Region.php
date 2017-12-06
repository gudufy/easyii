<?php

namespace yii\easyii\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "easyii_regions".
 *
 * @property string $id
 * @property string $name
 * @property int $parent_id
 * @property int $level
 */
class Region extends \yii\easyii\components\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'easyii_regions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name', 'parent_id', 'level'], 'required'],
            [['id', 'parent_id', 'level'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('easyii', 'ID'),
            'name' => Yii::t('easyii', 'Name'),
            'parent_id' => Yii::t('easyii', 'Parent ID'),
            'level' => Yii::t('easyii', 'Level'),
        ];
    }

    public static function getRegion($parentId=0)
    {
        $result = static::find()->where(['parent_id'=>$parentId])->asArray()->all();
        return ArrayHelper::map($result, 'id', 'name');
    }
}
