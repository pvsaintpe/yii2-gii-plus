<?php

namespace yii\gii\plus\generators\custom\model;

use yii\gii\CodeFile;
use yii\gii\Generator as GiiGenerator;
use yii\gii\plus\helpers\Helper;
use yii\web\JsExpression;
use yii\helpers\Json;
use Yii;

class Generator extends GiiGenerator
{

    /**
     * @var string
     */
    public $baseModelClass = 'app\models\base\*Base';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (Yii::getAlias('@common', false)) {
            $this->baseModelClass = 'common\models\base\*Base';
        }
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Custom Model Generator';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['baseModelClass'], 'filter', 'filter' => 'trim'],
            [['baseModelClass'], 'required'],
            [['baseModelClass'], 'match', 'pattern' => '~^(?:\w+\\\\)+base\\\\(?:\w+|\*)Base$~']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['model.php', 'query.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['baseModelClass']);
    }

    /**
     * @return JsExpression
     */
    public function getBaseModelClassAutoComplete()
    {
        $data = [];
        foreach (Helper::getModelDeepNamespaces() as $modelNs) {
            $data[] = $modelNs . '\base\*Base';
        }
        return new JsExpression('function (request, response) { response(' . Json::htmlEncode($data) . '); }');
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];
        if (preg_match('~^((?:\w+\\\\)*\w+)\\\\base\\\\(\w+|\*)Base$~', $this->baseModelClass, $match)) {
            $pattern = Yii::getAlias('@' . str_replace('\\', '/', $match[1])) . '/base/' . $match[2] . 'Base.php';
            foreach (glob($pattern) as $filename) {
                $ns = $match[1];
                $modelName = basename($filename, 'Base.php');
                $modelClass = $ns . '\\' . $modelName;
                $baseModelName = basename($filename, '.php');
                /* @var $baseModelClass string|\yii\boost\db\ActiveRecord */
                $baseModelClass = $ns . '\base\\' . $baseModelName;
                $queryNs = $ns . '\query';
                $queryName = $modelName . 'Query';
                $queryClass = $queryNs . '\\' . $queryName;
                $baseQueryName = $modelName . 'QueryBase';
                $baseQueryClass = $queryNs . '\base\\' . $baseQueryName;
                /* @var $tableSchema \yii\gii\plus\db\TableSchema */
                $tableSchema = $baseModelClass::getTableSchema();
                $params = [
                    'ns' => $ns,
                    'modelName' => $modelName,
                    'modelClass' => $modelClass,
                    'baseModelName' => $baseModelName,
                    'baseModelClass' => $baseModelClass,
                    'queryNs' => $queryNs,
                    'queryName' => $queryName,
                    'queryClass' => $queryClass,
                    'baseQueryName' => $baseQueryName,
                    'baseQueryClass' => $baseQueryClass,
                    'tableSchema' => $tableSchema
                ];
                // model
                $path = Yii::getAlias('@' . str_replace('\\', '/', $ns)) . '/' . $modelName . '.php';
                $files[] = new CodeFile($path, $this->render('model.php', $params));
                // query
                $path = Yii::getAlias('@' . str_replace('\\', '/', $queryNs)) . '/' . $queryName . '.php';
                $files[] = new CodeFile($path, $this->render('query.php', $params));
            }
        }
        return $files;
    }
}
