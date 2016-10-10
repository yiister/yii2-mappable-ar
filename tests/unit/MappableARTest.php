<?php

use data\Config;
use data\Config2;
use yii\db\ActiveRecord;

class MappableARTest extends \yii\codeception\TestCase
{
    public $appConfig = '@tests/unit/_config.php';

    protected function setUp()
    {
        Config::clearMap();
        parent::setUp();
    }

    public function testCleanMap()
    {
        $this->assertEquals(count(Config::getMap()), 0, 'No item in map');
    }

    public function testGetById()
    {
        $model = Config::getById(1);
        $this->assertTrue($model !== null, 'Item found');
        $this->assertTrue($model instanceof ActiveRecord, 'Item instance of ActiveRecord');
        $this->assertEquals(count(Config::getMap()), 1, '1 item in map');
        $row = Config::getById(2, true);
        $this->assertTrue(is_array($row), 'Item is array');
        $this->assertEquals(count(Config::getMap()), 2, '2 items in map');
        $modelArray = Config::getById(1, true);
        $this->assertSame($model->attributes, $modelArray);
    }

    public function testGetAll()
    {
        $models = Config::find()->asArray(true)->all();
        $this->assertEquals(count($models), 5, '5 items in map');
        $this->assertEquals(count(Config::getMap()), 5, '5 items in map');
        Config::clearMap();
        $this->assertEquals(count(Config::getMap()), 0, '0 items in map');
    }

    public function testGetByAttribute()
    {
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
        $model = Config::find()->select('value')->where(['id' => 1])->one();
        $this->assertTrue($model !== null, 'Item found');
        $this->assertEquals(count(Config::getMap()), 0, 'No item in map');
    }

    public function testFindBySql()
    {
        $model = Config::findBySql('SELECT * FROM config WHERE id = 1');
        $this->assertTrue($model !== null, 'Item found');
        $this->assertEquals(count(Config::getMap()), 0, 'No item in map');
    }

    public function testSave()
    {
        $model = Config::getById(1);
        $model->value = 'no-reply@example.com';
        $model->save();
        $model2 = Config::getById(1);
        $this->assertEquals($model->value, $model2->value, 'Model1 equal Model2');
        $model->key = '';
        $this->assertFalse($model->save());
        $model = Config::getById(1);
        $this->assertNotEmpty($model->key);
    }

    public function testAnotherId()
    {
        $model = Config2::getById('email.username');
        $model2 = Config::getById($model->id);
        $this->assertEquals($model->attributes, $model2->attributes);
    }

    public function testMaxLimit()
    {
        $limit = Config2::getIdentityMapMaxSize();
        $models = Config2::find()->limit($limit + 1)->all();
        $this->assertSame($limit + 1, count($models));
        $this->assertSame($limit, count(Config2::getMap()));
        Config2::find()->one();
        $this->assertSame($limit, count(Config2::getMap()));
    }

    public function testResort()
    {
        $model1 = Config2::getById('email.username');
        $model2 = Config2::getById('email.password', true);
        $model1 = Config2::getById('email.username', true);
        $modelMap = Config2::getMap();
        $model = array_shift($modelMap);
        $this->assertSame($model2, $model);
        $model = array_shift($modelMap);
        $this->assertSame($model1, $model);
    }
}
