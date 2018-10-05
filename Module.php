<?php

namespace pvsaintpe\gii\plus;

use pvsaintpe\gii\Module as GiiModule;
use pvsaintpe\gii\plus\helpers\Helper;
use yii\web\Application as WebApplication;
use Yii;

class Module extends GiiModule
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);
        
        foreach (Helper::getDbConnections() as $db) {
            if (in_array($db->getDriverName(), ['mysql', 'mysqli'])) {
                $db->schemaMap = array_merge($db->schemaMap, [
                    'mysql' => 'pvsaintpe\gii\plus\db\mysql\Schema',
                    'mysqli' => 'pvsaintpe\gii\plus\db\mysql\Schema'
                ]);
            }
        }
        if ($app instanceof WebApplication) {
            $this->setViewPath(Yii::getAlias('@pvsaintpe/gii/views'));
        }
    }

    /**
     * @inheritdoc
     */
    protected function coreGenerators()
    {
        return array_merge(parent::coreGenerators(), [
            'base_model' => ['class' => 'pvsaintpe\gii\plus\generators\base\model\Generator'],
            'custom_model' => ['class' => 'pvsaintpe\gii\plus\generators\custom\model\Generator'],
            'fixture' => ['class' => 'pvsaintpe\gii\plus\generators\fixture\Generator']
        ]);
    }
}
