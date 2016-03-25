<?php

use data\Config;
use yii\db\ActiveRecord;

class MappableARTest extends \yii\codeception\TestCase
{
    public $appConfig = '@tests/unit/_config.php';

    public function testCleanMap()
    {
        $this->assertEquals(count(Config::getMap()), 0, 'No item in map');
    }

    public function testGetById()
    {
        Config::clearMap();
        $model = Config::getById(1);
        $this->assertTrue($model !== null, 'Item found');
        $this->assertTrue($model instanceof ActiveRecord, 'Item instance of ActiveRecord');
        $this->assertEquals(count(Config::getMap()), 1, '1 item in map');
        $row = Config::getById(2, true);
        $this->assertTrue(is_array($row), 'Item is array');
        $this->assertEquals(count(Config::getMap()), 2, '2 items in map');
    }

    public function testGetAll()
    {
        Config::clearMap();
        $models = Config::find()->all();
        $this->assertEquals(count($models), 5, '5 items in map');
        $this->assertEquals(count(Config::getMap()), 5, '5 items in map');
        Config::clearMap();
        $this->assertEquals(count(Config::getMap()), 0, '0 items in map');
    }

    public function testGetByAttribute()
    {
        Config::clearMap();
        $model = Config::getByAttribute('key', 'email.smtp_address');
        $this->assertTrue($model !== null, 'Item found');
        $this->assertTrue($model instanceof ActiveRecord, 'Item instance of ActiveRecord');
        $this->assertEquals(count(Config::getMap()), 1, '1 item in map');
        $model2 = Config::getByAttribute('key', 'email.smtp_address', true);
        $this->assertTrue($model2 !== null, 'Item found');
        $this->assertTrue(is_array($model2), 'It is array');
        $this->assertEquals(count(Config::getMap()), 1, '1 item in map');
    }

    public function testSelectCustomFields()
    {
        Config::clearMap();
        $model = Config::find()->select('value')->where(['id' => 1])->one();
        $this->assertTrue($model !== null, 'Item found');
        $this->assertEquals(count(Config::getMap()), 0, 'No item in map');
    }

    public function testFindBySql()
    {
        Config::clearMap();
        $model = Config::findBySql('SELECT * FROM config WHERE id = 1');
        $this->assertTrue($model !== null, 'Item found');
        $this->assertEquals(count(Config::getMap()), 0, 'No item in map');
    }

    public function testSave()
    {
        Config::clearMap();
        $model = Config::getById(1);
        $model->value = 'no-reply@example.com';
        $model->save();
        $model2 = Config::getById(1);
        $this->assertEquals($model->value, $model2->value, 'Model1 equal Model2');
    }
}
