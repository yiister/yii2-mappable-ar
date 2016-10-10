<?php

/**
 * @copyright Copyright (c) 2015 Yiister
 * @license https://github.com/yiister/yii2-mappable-ar/blob/master/LICENSE.md
 * @link https://github.com/yiister/yii2-mappable-ar
 */

namespace yiister\mappable;

use Yii;
use yii\db\ActiveRecord;

/**
 * Class ActiveRecordTrait
 *
 * @mixed ActiveRecord
 *
 * @property string $idAttribute Name of a unique db field
 * @property integer $identityMapMaxSize Maximum items count at identity map
 * @property array $uniqueAttributes the list of attribute names for `getByAttribute` method. Each attribute must be a unique for column
 * @package yiister\mappable
 */
trait ActiveRecordTrait
{
    /** @var array List of loaded db rows. Key is a unique db field value. Value is an array of AR attributes */
    protected static $identityMap = [];

    /**
     * Get an attribute name of primary key.
     * @return string
     */
    public static function getIdAttribute()
    {
        return isset(static::$idAttribute) === true ? static::$idAttribute : 'id';
    }

    /**
     * Get a max count of identity map items.
     * @return integer
     */
    public static function getIdentityMapMaxSize()
    {
        return isset(static::$identityMapMaxSize) === true ? static::$identityMapMaxSize : -1;
    }

    /** @var array the conformity of a unique key to an id */
    protected static $uniqueAttributeToId = [];

    /**
     * Get list of unique attributes. It is used as a default value
     * @return array the empty list
     */
    public static function getUniqueAttributes()
    {
        return isset(static::$uniqueAttributes) ? static::$uniqueAttributes : [];
    }

    /**
     * Creates an [[ActiveQueryInterface]] instance for query purpose.
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function activeRecordTraitFind()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    /**
     * Creates an [[ActiveQueryInterface]] instance for query purpose.
     *
     * The returned [[ActiveQueryInterface]] instance can be further customized by calling
     * methods defined in [[ActiveQueryInterface]] before `one()` or `all()` is called to return
     * populated ActiveRecord instances. For example,
     *
     * ```php
     * // find the customer whose ID is 1
     * $customer = Customer::find()->where(['id' => 1])->one();
     *
     * // find all active customers and order them by their age:
     * $customers = Customer::find()
     *     ->where(['status' => 1])
     *     ->orderBy('age')
     *     ->all();
     * ```
     *
     * This method is also called by [[BaseActiveRecord::hasOne()]] and [[BaseActiveRecord::hasMany()]] to
     * create a relational query.
     *
     * You may override this method to return a customized query. For example,
     *
     * ```php
     * class Customer extends ActiveRecord
     * {
     *     public static function find()
     *     {
     *         // use CustomerQuery instead of the default ActiveQuery
     *         return new CustomerQuery(get_called_class());
     *     }
     * }
     * ```
     *
     * The following code shows how to apply a default condition for all queries:
     *
     * ```php
     * class Customer extends ActiveRecord
     * {
     *     public static function find()
     *     {
     *         return parent::find()->where(['deleted' => false]);
     *     }
     * }
     *
     * // Use andWhere()/orWhere() to apply the default condition
     * // SELECT FROM customer WHERE `deleted`=:deleted AND age>30
     * $customers = Customer::find()->andWhere('age>30')->all();
     *
     * // Use where() to ignore the default condition
     * // SELECT FROM customer WHERE age>30
     * $customers = Customer::find()->where('age>30')->all();
     *
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find()
    {
        return static::activeRecordTraitFind();
    }

    /**
     * Saves the current record.
     *
     * This method will call [[insert()]] when [[isNewRecord]] is true, or [[update()]]
     * when [[isNewRecord]] is false.
     *
     * For example, to save a customer record:
     *
     * ```php
     * $customer = new Customer; // or $customer = Customer::findOne($id);
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->save();
     * ```
     *
     * @param boolean $runValidation whether to perform validation (calling [[validate()]])
     * before saving the record. Defaults to `true`. If the validation fails, the record
     * will not be saved to the database and this method will return `false`.
     * @param array $attributeNames list of attribute names that need to be saved. Defaults to null,
     * meaning all attributes that are loaded from DB will be saved.
     * @return boolean whether the saving succeeded (i.e. no validation errors occurred).
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if (parent::save($runValidation, $attributeNames) === false) {
            return false;
        }
        static::addRowToMap($this);
        return true;
    }

    /**
     * Add a one row to identity map
     * @param ActiveRecord | array $row
     */
    public static function addRowToMap($row)
    {
        $id = static::getIdAttribute();
        if ($row !== null && isset($row[$id])) {
            self::$identityMap[$row[$id]] = $row instanceof ActiveRecord ? $row->toArray() : $row;
            $maxSize = static::getIdentityMapMaxSize();
            foreach (static::getUniqueAttributes() as $uniqueAttribute) {
                self::$uniqueAttributeToId[$uniqueAttribute][$row[$uniqueAttribute]] = $row[$id];
            }
            if ($maxSize !== -1 && count(self::$identityMap) > $maxSize) {
                array_shift(self::$identityMap);
            }
        }
    }

    /**
     * Add rows to identity map
     * @param ActiveRecord[]|array[] $rows
     */
    public static function addRowsToMap($rows)
    {
        $firstRow = reset($rows);
        $idAttribute = static::getIdAttribute();
        if ($firstRow instanceof ActiveRecord) {
            foreach ($rows as $row) {
                self::$identityMap[$row[$idAttribute]] = $row->toArray();
                foreach (static::getUniqueAttributes() as $uniqueAttribute) {
                    self::$uniqueAttributeToId[$uniqueAttribute][$row[$uniqueAttribute]] = $row[$idAttribute];
                }
            }
        } else {
            foreach ($rows as $row) {
                self::$identityMap[$row[$idAttribute]] = $row;
                foreach (static::getUniqueAttributes() as $uniqueAttribute) {
                    self::$uniqueAttributeToId[$uniqueAttribute][$row[$uniqueAttribute]] = $row[$idAttribute];
                }
            }
        }
        $maxSize = self::getIdentityMapMaxSize();
        if ($maxSize !== -1) {
            $count = count(self::$identityMap);
            if ($count > $maxSize) {
                self::$identityMap = array_slice(
                    self::$identityMap,
                    $count - $maxSize,
                    $maxSize
                );
            }
        }
    }

    /**
     * Get a single record by id
     * @param string|int $id
     * @param bool $asArray Return a result as array
     * @return array|null|ActiveRecord
     */
    public static function getById($id, $asArray = false)
    {
        if (isset(self::$identityMap[$id])) {
            if (self::getIdentityMapMaxSize() !== -1) {
                $row = self::$identityMap[$id];
                unset(self::$identityMap[$id]);
                self::$identityMap[$id] = $row;
            }
            if ($asArray) {
                return self::$identityMap[$id];
            } else {
                $model = new static;
                /** @var ActiveRecord $modelClass */
                $modelClass = get_class($model);
                $modelClass::populateRecord($model, self::$identityMap[$id]);
                return $model;
            }
        } else {
            $row = static::find()
                ->where([static::getIdAttribute() => $id])
                ->asArray($asArray)
                ->one();
            static::addRowToMap($row);
            return $row;
        }
    }

    /**
     * Get a single record by unique attribute
     * @param string $attribute
     * @param mixed $value
     * @param bool $asArray
     * @return array|null|ActiveRecord
     */
    public static function getByAttribute($attribute, $value, $asArray = false)
    {
        if (isset(self::$uniqueAttributeToId[$attribute][$value])) {
            return static::getById(self::$uniqueAttributeToId[$attribute][$value], $asArray);
        }
        $row = static::find()
            ->where([$attribute => $value])
            ->asArray($asArray)
            ->one();
        static::addRowToMap($row);
        return $row;
    }

    /**
     * Get a current identity map array
     * @return array
     */
    public static function getMap()
    {
        return self::$identityMap;
    }

    /**
     * Clear an identity map array
     */
    public static function clearMap()
    {
        self::$identityMap = [];
        self::$uniqueAttributeToId = [];
    }
}
