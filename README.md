# Yii2 mappable ActiveRecord

It is an extension for Yii framework 2 that gives an ability to use identity map for any ActiveRecord model.

[![Build Status](https://travis-ci.org/yiister/yii2-mappable-ar.svg?branch=master)](https://travis-ci.org/yiister/yii2-mappable-ar)
[![codecov.io](https://codecov.io/github/yiister/yii2-mappable-ar/coverage.svg?branch=master)](https://codecov.io/github/yiister/yii2-mappable-ar?branch=master)

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

Using
-----

Just add

```php
use yiister\mappable\ActiveRecordTrait;
```

to your model. After you may use `getById` and `getByAttribute` methods to get a model or an array.
