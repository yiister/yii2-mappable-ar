<?php

/**
 * @copyright Copyright (c) 2015 Yiister
 * @license https://github.com/yiister/yii2-mappable-ar/blob/master/LICENSE.md
 * @link https://github.com/yiister/yii2-mappable-ar
 */

namespace yiister\mappable;

use yii\db\ActiveRecord;

/**
 * Class ActiveQuery
 * @package yiister\mappable
 */
class ActiveQuery extends \yii\db\ActiveQuery
{
    /**
     * Is this query mappable?
     * @return bool
     */
    protected function isMappableQuery()
    {
        return $this->select === null && $this->sql === null;
    }

    /**
     * @inheritdoc
     */
    public function one($db = null)
    {
        $row = parent::one($db);
        if ($row !== null && $this->isMappableQuery()) {
            /** @var ActiveRecord|ActiveRecordTrait $className */
            $className = $this->modelClass;
            $className::addRowToMap($row);
        }
        return $row;
    }

    /**
     * @inheritdoc
     */
    public function all($db = null)
    {
        $rows = parent::all($db);
        if (count($rows) > 0 && $this->isMappableQuery()) {
            /** @var ActiveRecord|ActiveRecordTrait $className */
            $className = $this->modelClass;
            $className::addRowsToMap($rows);
        }
        return $rows;
    }
}
