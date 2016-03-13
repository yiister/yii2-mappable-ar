<?php

/**
 * @copyright Copyright (c) 2015 Yiister
 * @license https://github.com/yiister/yii2-mappable-ar/blob/master/LICENSE
 * @link https://github.com/yiister/yii2-mappable-ar
 */

namespace yiister\mappable;

use Yii;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;

trait ActiveRecordTrait
{
    /** @var array List of loaded db rows. Key is a unique db field value. Value is an array of AR attributes */
    public static $identityMap = [];

    /** @var string Name of a unique db field */
    public static $idAttribute = 'id';

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
     * @return ActiveQueryInterface the newly created [[ActiveQueryInterface]] instance.
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    /**
     * Add a one row to identity map
     * @param ActiveRecord | array $row
     */
    public static function addRowToMap($row)
    {
        if ($row !== null && isset($row[self::$idAttribute])) {
            self::$identityMap[$row[self::$idAttribute]] = $row instanceof ActiveRecord ? $row->toArray() : $row;
        }
    }

    /**
     * Add rows to identity map
     * @param ActiveRecord[]|array[] $rows
     */
    public static function addRowsToMap($rows)
    {
        $firstRow = reset($rows);
        if ($firstRow instanceof ActiveRecord) {
            foreach ($rows as $row) {
                self::$identityMap[$row[self::$idAttribute]] = $row->toArray();
            }
        } else {
            foreach ($rows as $row) {
                self::$identityMap[$row[self::$idAttribute]] = $row;
            }
        }
    }

    /**
     * Find a single record by id
     * @param string|int $id
     * @param bool $asArray Return a result as array
     * @return array|null|ActiveRecord
     */
    public static function findById($id, $asArray = false)
    {
        if (isset(self::$identityMap[$id])) {
            if ($asArray) {
                return self::$identityMap[$id];
            } else {
                $record = new static;
                return static::populateRecord($record, self::$identityMap[$id]);
            }
        } else {
            $row = static::find()
                ->where([self::$idAttribute => $id])
                ->asArray($asArray)
                ->one();
            static::addRowToMap($row);
            return $row;
        }
    }
}
