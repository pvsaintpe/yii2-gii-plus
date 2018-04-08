<?php

use yii\gii\plus\helpers\Helper;
use yii\helpers\Inflector;

/* @var $this yii\web\View */
/* @var $generator yii\gii\plus\generators\custom\model\Generator */
/* @var $ns string */
/* @var $modelName string */
/* @var $modelClass string|yii\boost\db\ActiveRecord */
/* @var $baseModelName string */
/* @var $baseModelClass string|yii\boost\db\ActiveRecord */
/* @var $queryNs string */
/* @var $queryName string */
/* @var $queryClass string|yii\boost\db\ActiveQuery */
/* @var $baseQueryName string */
/* @var $baseQueryClass string|yii\boost\db\ActiveQuery */
/* @var $tableSchema yii\gii\plus\db\TableSchema */

$uses = [
    $baseQueryClass
];
Helper::sortUses($uses);

echo '<?php

namespace ', $queryNs, ';

use ', implode(';' . "\n" . 'use ', $uses), ';

/**
 * ', Inflector::titleize($queryName), '
 * @see \\', $modelClass, '
 */
class ', $queryName, ' extends ', $baseQueryName, '
{
}
';
