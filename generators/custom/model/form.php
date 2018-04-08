<?php

use yii\jui\autosearch\AutoComplete;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\plus\generators\custom\model\Generator */

echo $form->field($generator, 'baseModelClass')->widget(AutoComplete::className(), [
    'source' => $generator->getBaseModelClassAutoComplete()
]);
