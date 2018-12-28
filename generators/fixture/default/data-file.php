<?php

use yii\helpers\Inflector;

/* @var $this yii\web\View */
/* @var $generator pvsaintpe\gii\plus\generators\fixture\Generator */
/* @var $ns string */
/* @var $modelName string */
/* @var $modelClass string|pvsaintpe\boost\db\ActiveRecord */
/* @var $fixtureNs string */
/* @var $fixtureName string */
/* @var $fixtureClass string|pvsaintpe\boost\test\ActiveFixture */
/* @var $baseFixtureName string */
/* @var $baseFixtureClass string|pvsaintpe\boost\test\ActiveFixture */
/* @var $dataFile string */
/* @var $tableSchema pvsaintpe\db\components\TableSchema */

echo '<?php

/* ', Inflector::titleize($fixtureName), ' data-file */
/* @see ', $fixtureClass, ' */
/* @see ', $modelClass, ' */

return [
    /*[
';
/* @var $columns pvsaintpe\db\components\ColumnSchema[] */
$columns = array_values($tableSchema->columns);
foreach ($columns as $i => $column) {
    $comma = ($i < count($columns) - 1) ? ',' : '';
    echo '        \'', $column->name, '\' => \'\'', $comma, '
';
}
echo '    ]*/
];
';
