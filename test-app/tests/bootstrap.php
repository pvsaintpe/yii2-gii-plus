<?php

$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SERVER['SCRIPT_NAME'] = '/' . basename(__FILE__);
$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@tests', __DIR__);
Yii::setAlias('@yii/gii/plus', dirname(dirname(__DIR__)));
