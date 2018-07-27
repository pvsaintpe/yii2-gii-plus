<?php

use yii\jui\autosearch\AutoComplete;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\plus\generators\fixture\Generator */

echo $form->field($generator, 'modelClass')->widget(AutoComplete::class, [
    'source' => $generator->getModelClassAutoComplete()
]);
echo $form->field($generator, 'fixtureNs');
echo $form->field($generator, 'fixtureBaseClass')->widget(AutoComplete::class, [
    'source' => $generator->getFixtureBaseClassAutoComplete()
]);
echo $form->field($generator, 'generateDataFile')->checkbox();
echo $form->field($generator, 'dataPath');
