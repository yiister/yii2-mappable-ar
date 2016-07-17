Yii2 mappable ActiveRecord
==========================

It is an extension for Yii framework 2 that gives an ability to use identity map for any ActiveRecord model.

[![Build Status](https://travis-ci.org/yiister/yii2-mappable-ar.svg?branch=master)](https://travis-ci.org/yiister/yii2-mappable-ar)
[![codecov.io](https://codecov.io/github/yiister/yii2-mappable-ar/coverage.svg?branch=master)](https://codecov.io/github/yiister/yii2-mappable-ar?branch=master)


[Russian documentation](docs/ru).

How it works
------------

`ActiveRecordTrait` overrides a `find` method `id` of model. This method creates a custom `ActiveQuery`. When `one` (`all`) method is called, a got model (models) save to `identityMap` as array of attributes (It saves a memory). The next requests return data without queries to data base.

By the way the next methods are allowed:

- `getById(integer $id, boolean $asArray = false)` - get a model or an array of attributes (It depends on second param value) by primary key;
- `getByAttribute(string $attribute, string $value, boolean $asArray = false)` - get a model or an array of attributes (It depends on second param value) by unique attribute value; 
- `getMap()` - get all models from `identityMap` as array of attributes;
- `clearMap()` - clear an `identityMap`.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist yiister/yii2-mappable-ar
```

or add

```json
"yiister/yii2-mappable-ar": "~1.0.0"
```

to the `require` section of your composer.json.

Setting
-------

The extension supports the next settings:

- `idAttribute` - the primary key attribute (by default `id`)
- `identityMapMaxSize` - the maximum elements count in identityMap (by default `-1` = no limit)

For example, for change a primary key attribute to `key` add to your model `public static $idAttribute = 'key';`.

Using
-----

Just add `use yiister\mappable\ActiveRecordTrait;` to your model for using an identityMap. You got all features after it.

**Warn!** Trait does not work if you override a `find` method in your model. This problem has a issue #7 on github.