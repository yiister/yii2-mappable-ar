<?php

namespace data;

use Yii;
use yiister\mappable\ActiveRecordTrait;

/**
 * This is the model class for table "config".
 *
 * @property integer $id
 * @property string $key
 * @property string $value
 * @property string $description
 */
class Config2 extends \yii\db\ActiveRecord
{
    use ActiveRecordTrait;

    public static $idAttribute = 'key';

    public static $identityMapMaxSize = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'value'], 'required'],
            [['key'], 'string', 'max' => 100],
            [['value', 'description'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'value' => 'Value',
            'description' => 'Description',
        ];
    }
}
