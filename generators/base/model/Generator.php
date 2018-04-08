<?php

namespace yii\gii\plus\generators\base\model;

use yii\base\ErrorException;
use yii\db\Expression;
use yii\gii\generators\model\Generator as GiiModelGenerator;
use yii\gii\plus\helpers\Helper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\web\JsExpression;
use yii\helpers\Json;
use Yii;

class Generator extends GiiModelGenerator
{

    /**
     * @var string
     */
    public $includeFilter = '.*';

    /**
     * @var string
     */
    public $excludeFilter = '(?:\w+\.)?(?:migration|cache|source_message|message|log|auth_\w+)';

    public $ns = 'app\models\base';
    public $tableName = '*';
    public $baseClass = 'yii\boost\db\ActiveRecord';
    public $generateLabelsFromComments = true;
    public $useSchemaName = false;
    public $generateQuery = true;
    public $queryNs;
    public $queryBaseClass = 'yii\boost\db\ActiveQuery';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (Yii::getAlias('@common', false)) {
            $this->ns = 'common\models\base';
        }
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Base Model Generator';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [];
        foreach (parent::rules() as $rule) {
            if (!is_array($rule[0])) {
                $rule[0] = [$rule[0]];
            }
            if ($rule[1] == 'required') {
                $rule[0] = array_diff($rule[0], ['queryNs']);
            }
            if (count($rule[0])) {
                $rules[] = $rule;
            }
        }
        return array_merge($rules, [
            [['includeFilter', 'excludeFilter'], 'filter', 'filter' => 'trim'],
            [['includeFilter', 'excludeFilter'], 'required'],
            [['includeFilter', 'excludeFilter'], 'validatePattern'],
            [['ns'], 'match', 'pattern' => '~\\\\base$~'],
            [['modelClass'], 'match', 'pattern' => '~Base$~'],
            [['baseClass'], 'validateClass', 'params' => ['extends' => 'yii\boost\db\ActiveRecord']],
            [['queryNs'], 'default', 'value' => function (Generator $model, $attribute) {
                return preg_replace('~\\\\base$~', '\query\base', $model->ns);
            }],
            [['queryNs'], 'match', 'pattern' => '~\\\\query\\\\base$~'],
            [['queryClass'], 'match', 'pattern' => '~QueryBase$~'],
            [['queryBaseClass'], 'validateClass', 'params' => ['extends' => 'yii\boost\db\ActiveQuery']]
        ]);
    }

    /**
     * @param string $attribute
     * @param array $params
     */
    public function validatePattern($attribute, $params)
    {
        if (!$this->hasErrors($attribute)) {
            try {
                preg_match('~^(?:' . $this->$attribute . ')$~', 'migration');
            } catch (ErrorException $exception) {
                $this->addError($attribute, $exception->getMessage());
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function autoCompleteData()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['model.php', 'model-part.php', 'query.php', 'query-part.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(array_diff(parent::stickyAttributes(), ['queryNs']), [
            'includeFilter',
            'excludeFilter'
        ]);
    }

    /**
     * @param array $data
     * @return JsExpression
     */
    protected function createAutoComplete(array $data)
    {
        $js = 'function (request, response) { response(' . Json::htmlEncode($data) .
            '[jQuery(\'#' . Html::getInputId($this, 'db') . '\').val()]); }';
        return new JsExpression($js);
    }

    /**
     * @param bool $refresh
     * @return JsExpression
     */
    public function getTableNameAutoComplete($refresh = false)
    {
        $data = [];
        foreach (Helper::getDbConnections() as $id => $db) {
            $data[$id] = ['*'];
            $schemaNames = Helper::getSchemaNames($db, $refresh);
            foreach ($schemaNames as $schemaName) {
                $data[$id][] = $schemaName . '.*';
            }
            $schema = $db->getSchema();
            foreach ($schema->getTableNames('', $refresh) as $tableName) {
                $data[$id][] = $tableName;
            }
            foreach ($schemaNames as $schemaName) {
                foreach ($schema->getTableNames($schemaName, $refresh) as $tableName) {
                    $data[$id][] = $schemaName . '.' . $tableName;
                }
            }
        }
        return $this->createAutoComplete($data);
    }

    /**
     * @param bool $refresh
     * @return JsExpression
     */
    public function getNsAutoComplete($refresh = false)
    {
        $data = [];
        foreach (Helper::getDbConnections() as $id => $db) {
            $data[$id] = [];
            $schemaNames = Helper::getSchemaNames($db, $refresh);
            foreach (Helper::getModelNamespaces() as $modelNs) {
                $data[$id][] = $modelNs . '\base';
                foreach ($schemaNames as $schemaName) {
                    $data[$id][] = $modelNs . '\\' . $schemaName . '\base';
                }
                $data[$id][] = $modelNs . '\\' . $id . '\base';
                foreach ($schemaNames as $schemaName) {
                    $data[$id][] = $modelNs . '\\' . $id . '\\' . $schemaName . '\base';
                }
            }
        }
        return $this->createAutoComplete($data);
    }

    /**
     * @return string[]
     */
    public function getBaseClassAutoComplete()
    {
        return ['yii\boost\db\ActiveRecord'];
    }

    /**
     * @return array
     */
    public function getDbListItems()
    {
        $ids = array_keys(Helper::getDbConnections());
        return array_combine($ids, $ids);
    }

    /**
     * @param bool $refresh
     * @return JsExpression
     */
    public function getQueryNsAutoComplete($refresh = false)
    {
        $data = [];
        foreach (Helper::getDbConnections() as $id => $db) {
            $data[$id] = [];
            $schemaNames = Helper::getSchemaNames($db, $refresh);
            foreach (Helper::getModelNamespaces() as $modelNs) {
                $data[$id][] = $modelNs . '\query\base';
                foreach ($schemaNames as $schemaName) {
                    $data[$id][] = $modelNs . '\\' . $schemaName . '\query\base';
                }
                $data[$id][] = $modelNs . '\\' . $id . '\query\base';
                foreach ($schemaNames as $schemaName) {
                    $data[$id][] = $modelNs . '\\' . $id . '\\' . $schemaName . '\query\base';
                }
            }
        }
        return $this->createAutoComplete($data);
    }

    /**
     * @return string[]
     */
    public function getQueryBaseClassAutoComplete()
    {
        return ['yii\boost\db\ActiveQuery'];
    }

    /**
     * @var string
     */
    protected $commonBaseClass;

    /**
     * @var string
     */
    protected $commonQueryBaseClass;

    /**
     * @var bool
     */
    protected $relationsDone = false;

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $this->commonBaseClass = $this->baseClass;
        $this->commonQueryBaseClass = $this->queryBaseClass;
        $this->relationsDone = false;
        $this->classNames = [];
        $files = parent::generate();
        $this->baseClass = $this->commonBaseClass;
        $this->queryBaseClass = $this->commonQueryBaseClass;
        return $files;
    }

    /**
     * @inheritdoc
     * @param \yii\gii\plus\db\TableSchema $table
     */
    public function generateRules($table)
    {
        $booleanAttributes = [];
        $integerAttributes = [];
        $uIntegerAttributes = [];
        $numberAttributes = [];
        $uNumberAttributes = [];
        $dateFormats = [];
        $matchPatterns = [];
        $defaultExpressions = [];
        $defaultValues = [];
        $defaultNullAttributes = [];
        foreach ($table->columns as $column) {
            if ($column->autoIncrement) {
                continue;
            }
            if ($column->getIsBoolean()) {
                $booleanAttributes[] = $column->name;
            } elseif ($column->getIsInteger()) {
                if ($column->unsigned) {
                    $uIntegerAttributes[] = $column->name;
                } else {
                    $integerAttributes[] = $column->name;
                }
            } elseif ($column->getIsNumber()) {
                if ($column->unsigned) {
                    $uNumberAttributes[] = $column->name;
                } else {
                    $numberAttributes[] = $column->name;
                }
            }
            if ($column->getHasDateFormat()) {
                $dateFormats[$column->getDateFormat()][] = $column->name;
            }
            if ($column->getHasPattern()) {
                $matchPatterns[$column->getPattern()][] = $column->name;
            }
            if (!is_null($column->defaultValue)) {
                if ($column->defaultValue instanceof Expression) {
                    $this->relationUses[$table->fullName][] = 'yii\db\Expression';
                    $defaultExpressions[$column->defaultValue->expression][] = $column->name;
                } else {
                    $defaultValues[$column->defaultValue][] = $column->name;
                }
            } elseif ($column->allowNull) {
                $defaultNullAttributes[] = $column->name;
            }
        }
        $rules = [];
        if (count($booleanAttributes)) {
            $rules[] = '[' . Helper::implode($booleanAttributes, 3) . ', \'filter\', \'filter\' => function ($value) {' . "\n" . '                return $value ? 1 : 0;' . "\n" . '            }, \'skipOnEmpty\' => true]';
            $rules[] = '[' . Helper::implode($booleanAttributes, 3) . ', \'boolean\']';
        }
        if (count($integerAttributes)) {
            $rules[] = '[' . Helper::implode($integerAttributes, 3) . ', \'integer\']';
        }
        if (count($uIntegerAttributes)) {
            $rules[] = '[' . Helper::implode($uIntegerAttributes, 3) . ', \'integer\', \'min\' => 0]';
        }
        if (count($numberAttributes)) {
            $rules[] = '[' . Helper::implode($numberAttributes, 3) . ', \'number\']';
        }
        if (count($uNumberAttributes)) {
            $rules[] = '[' . Helper::implode($uNumberAttributes, 3) . ', \'number\', \'min\' => 0]';
        }
        foreach ($dateFormats as $dateFormat => $attributes) {
            $rules[] = '[' . Helper::implode($attributes, 3) . ', \'filter\', \'filter\' => function ($value) {
                return is_int($value) ? date(\'' . substr($dateFormat, 4) . '\', $value) : $value;
            }]';
        }
        foreach ($dateFormats as $dateFormat => $attributes) {
            $rules[] = '[' . Helper::implode($attributes, 3) . ', \'date\', \'format\' => \'' . $dateFormat . '\']';
        }
        foreach ($matchPatterns as $matchPattern => $attributes) {
            $rules[] = '[' . Helper::implode($attributes, 3) . ', \'match\', \'pattern\' => \'' . $matchPattern . '\']';
        }
        foreach (parent::generateRules($table) as $rule) {
            if (preg_match('~, \'(?:safe|boolean|integer|number)\'\]$~', $rule)) {
                continue;
            }
            $rules[] = $rule;
        }
        foreach ($defaultExpressions as $defaultExpression => $attributes) {
            $rules[] = '[' . Helper::implode($attributes, 3) . ', \'default\', \'value\' => new Expression(\'' . $defaultExpression . '\')]';
        }
        foreach ($defaultValues as $defaultValue => $attributes) {
            $rules[] = '[' . Helper::implode($attributes, 3) . ', \'default\', \'value\' => \'' . $defaultValue . '\']';
        }
        if (count($defaultNullAttributes)) {
            $rules[] = '[' . Helper::implode($defaultNullAttributes, 3) . ', \'default\', \'value\' => null]';
        }
        return $rules;
    }

    /**
     * @var array
     */
    protected $relationUses = [];

    /**
     * @var array
     */
    protected $allRelations = [];

    /**
     * @var array
     */
    protected $singularRelations = [];

    /**
     * @var array
     */
    protected $pluralRelations = [];

    /**
     * @param array $generatedRelations
     * @return array
     */
    protected function fixRelations(array $generatedRelations)
    {
        foreach ($generatedRelations as $tableName => $tableRelations) {
            $fixRelationNames = [];
            foreach ($tableRelations as $relationName => $relation) {
                if (preg_match('~^(\D+)\d+$~', $relationName, $match)) {
                    $fixRelationNames[] = $match[1];
                }
            }
            foreach ($fixRelationNames as $fixRelationName) {
                foreach ($tableRelations as $relationName => $relation) {
                    if ($relationName == $fixRelationName) {
                        if (isset($tableRelations[$relationName])) {
                            $tableRelations[$relationName . '99'] = $tableRelations[$relationName];
                            unset($tableRelations[$relationName]);
                        }
                        if (isset($generatedRelations[$tableName][$relationName])) {
                            $generatedRelations[$tableName][$relationName . '99'] = $generatedRelations[$tableName][$relationName];
                            unset($generatedRelations[$tableName][$relationName]);
                        }
                        if (isset($this->allRelations[$tableName][$relationName])) {
                            $this->allRelations[$tableName][$relationName . '99'] = $this->allRelations[$tableName][$relationName];
                            unset($this->allRelations[$tableName][$relationName]);
                        }
                        if (isset($this->singularRelations[$tableName][$relationName])) {
                            $this->singularRelations[$tableName][$relationName . '99'] = $this->singularRelations[$tableName][$relationName];
                            unset($this->singularRelations[$tableName][$relationName]);
                        }
                        if (isset($this->pluralRelations[$tableName][$relationName])) {
                            $this->pluralRelations[$tableName][$relationName . '99'] = $this->pluralRelations[$tableName][$relationName];
                            unset($this->pluralRelations[$tableName][$relationName]);
                        }
                    }
                }
            }
            foreach ($fixRelationNames as $fixRelationName) {
                foreach ($tableRelations as $relationName => $relation) {
                    if (preg_match('~^(\D+)\d+$~', $relationName, $match) && ($match[1] == $fixRelationName)) {
                        $relation = $this->allRelations[$tableName][$relationName];
                        if ($relation['hasMany']) {
                            $linkKeys = array_keys($relation['link']);
                            if (count($linkKeys) == 1) {
                                $linkKey = preg_replace('~_id$~', '', $linkKeys[0]);
                                if ($tableName == $linkKey) {
                                    $relationName2 = $fixRelationName;
                                } else {
                                    $relationName2 = Inflector::classify($linkKey);
                                    $relationName2 = str_replace(Inflector::singularize($fixRelationName), '', $relationName2);
                                    $relationName2 .= $fixRelationName;
                                }
                                echo $relationName, ' -> ', $relationName2, "\n";
                                if (isset($tableRelations[$relationName])) {
                                    $tableRelations[$relationName2] = $tableRelations[$relationName];
                                    unset($tableRelations[$relationName]);
                                }
                                if (isset($generatedRelations[$tableName][$relationName])) {
                                    $generatedRelations[$tableName][$relationName2] = $generatedRelations[$tableName][$relationName];
                                    unset($generatedRelations[$tableName][$relationName]);
                                }
                                if (isset($this->allRelations[$tableName][$relationName])) {
                                    $this->allRelations[$tableName][$relationName2] = $this->allRelations[$tableName][$relationName];
                                    unset($this->allRelations[$tableName][$relationName]);
                                }
                                if (isset($this->singularRelations[$tableName][$relationName])) {
                                    $this->singularRelations[$tableName][$relationName2] = $this->singularRelations[$tableName][$relationName];
                                    unset($this->singularRelations[$tableName][$relationName]);
                                }
                                if (isset($this->pluralRelations[$tableName][$relationName])) {
                                    $this->pluralRelations[$tableName][$relationName2] = $this->pluralRelations[$tableName][$relationName];
                                    unset($this->pluralRelations[$tableName][$relationName]);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $generatedRelations;
    }

    /**
     * @inheritdoc
     */
    protected function generateRelations()
    {
        $db = $this->getDbConnection();
        $relations = [];
        $this->relationUses = [];
        $this->allRelations = [];
        $this->singularRelations = [];
        $this->pluralRelations = [];
        $generatedRelations = parent::generateRelations();
        foreach ($generatedRelations as $tableName => $tableRelations) {
            /* @var $tableSchema \yii\gii\plus\db\TableSchema */
            $tableSchema = $db->getTableSchema($tableName);
            $relations[$tableName] = [];
            $this->relationUses[$tableName] = [];
            $this->allRelations[$tableName] = [];
            $this->singularRelations[$tableName] = [];
            $this->pluralRelations[$tableName] = [];
            foreach ($tableRelations as $relationName => $relation) {
                list ($code, $className, $hasMany) = $relation;
                /* @var $nsClassName string|\yii\boost\db\ActiveRecord */
                $nsClassName = Helper::getModelClassByTableName(array_search($className, $this->classNames));
                if ($nsClassName && class_exists($nsClassName)) {
                    $relations[$tableName][$relationName] = [$code, $className, $hasMany];
                    $this->relationUses[$tableName][] = $nsClassName;
                    // extended relations
                    $subTableSchema = $nsClassName::getTableSchema();
                    $subTableName = $subTableSchema->fullName;
                    // link
                    $link = [];
                    $direct = null;
                    if ($hasMany) {
                        foreach ($subTableSchema->foreignKeys as $foreignKey) {
                            if ($foreignKey[0] == $tableName) {
                                unset($foreignKey[0]);
                                $refs = $foreignKey;
                                if (strpos($code, $this->generateRelationLink($refs)) != false) {
                                    $link = $refs;
                                    $direct = false;
                                    break;
                                }
                            }
                        }
                    } else {
                        foreach ($tableSchema->foreignKeys as $foreignKey) {
                            if ($foreignKey[0] == $subTableName) {
                                unset($foreignKey[0]);
                                $refs = array_flip($foreignKey);
                                if (strpos($code, $this->generateRelationLink($refs)) != false) {
                                    $link = $refs;
                                    $direct = true;
                                    break;
                                }
                            }
                        }
                        if (!count($link)) {
                            foreach ($subTableSchema->foreignKeys as $foreignKey) {
                                if ($foreignKey[0] == $tableName) {
                                    unset($foreignKey[0]);
                                    $refs = $foreignKey;
                                    if (strpos($code, $this->generateRelationLink($refs)) != false) {
                                        $link = $refs;
                                        $direct = false;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    $viaTable = false;
                    if (preg_match('~\-\>viaTable\(\'(\w+)(?:\'| )~', $code, $match)) {
                        $viaTable = $match[1];
                    }
                    $linkCode = $this->generateRelationLink($link);
                    $this->allRelations[$tableName][$relationName] = compact(
                        'code', 'className', 'hasMany',
                        'nsClassName', 'link', 'direct', 'viaTable', 'linkCode'
                    );
                    if ($hasMany) {
                        $this->pluralRelations[$tableName][$relationName] = compact(
                            'code', 'className', 'hasMany',
                            'nsClassName', 'link', 'direct', 'viaTable', 'linkCode'
                        );
                    } else {
                        $this->singularRelations[$tableName][$relationName] = compact(
                            'code', 'className', 'hasMany',
                            'nsClassName', 'link', 'direct', 'viaTable', 'linkCode'
                        );
                    }
                    // via relations
                    if (!$hasMany && ($subTableName != $tableName)) {
                        foreach ($generatedRelations[$subTableName] as $subRelationName => $subRelation) {
                            list ($subCode, $subClassName, $subHasMany) = $subRelation;
                            $tableName2 = array_search($subClassName, $this->classNames);
                            if ($tableName2 != $tableName) {
                                /* @var $subNsClassName string|\yii\boost\db\ActiveRecord */
                                $subNsClassName = Helper::getModelClassByTableName($tableName2);
                                if ($subNsClassName && class_exists($subNsClassName)) {
                                    if (!$subHasMany && !array_key_exists($subRelationName, $generatedRelations[$tableName])) {
                                        $viaLink = $this->generateRelationLink($link);
                                        $subCode = preg_replace('~;$~', "\n" . '            ->viaTable(\'' . $subTableName . ' via_' . $subTableName . '\', ' . $viaLink . ');', $subCode);
                                        if (!array_key_exists($subRelationName, $relations[$tableName]) || $direct) {
                                            $relations[$tableName][$subRelationName] = [$subCode, $subClassName, $subHasMany];
                                            $this->relationUses[$tableName][] = $subNsClassName;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->relationsDone = true;
        $this->classNames = [];
        return $this->fixRelations($relations);
    }

    /**
     * @inheritdoc
     * @param \yii\gii\plus\db\TableSchema $table
     */
    protected function generateRelationName($relations, $table, $key, $multiple)
    {
        if ($table->isView) {
            $key = preg_replace('~^(?:[pfut]k_)?(\w+_id)$~', '$1', $key);
        }
        return parent::generateRelationName($relations, $table, $key, $multiple);
    }

    /**
     * @inheritdoc
     */
    protected function getTableNames()
    {
        try {
            $this->tableNames = array_filter(parent::getTableNames(), function ($tableName) {
                return preg_match('~^(?:' . $this->includeFilter . ')$~i', $tableName) && !preg_match('~^(?:' . $this->excludeFilter . ')$~i', $tableName);
            });
        } catch (ErrorException $e) {
        }
        return $this->tableNames;
    }

    /**
     * @inheritdoc
     */
    protected function generateClassName($tableName, $useSchemaName = null)
    {
        if (!$this->relationsDone) {
            return parent::generateClassName($tableName, $useSchemaName);
        }
        $className = parent::generateClassName($tableName, $useSchemaName) . 'Base';
        if ($this->commonBaseClass) {
            $nsClassName = $this->ns . '\\' . $className;
            if (class_exists($nsClassName)) {
                $this->baseClass = get_parent_class($nsClassName);
            } else {
                $this->baseClass = $this->commonBaseClass;
            }
        }
        return $className;
    }

    /**
     * @inheritdoc
     */
    protected function generateQueryClassName($modelClassName)
    {
        $queryClassName = parent::generateQueryClassName(preg_replace('~Base$~', '', $modelClassName)) . 'Base';
        if ($this->commonQueryBaseClass) {
            $nsQueryClassName = $this->queryNs . '\\' . $queryClassName;
            if (class_exists($nsQueryClassName)) {
                $this->queryBaseClass = get_parent_class($nsQueryClassName);
            } else {
                $this->queryBaseClass = $this->commonQueryBaseClass;
            }
        }
        return $queryClassName;
    }

    /**
     * @inheritdoc
     */
    public function render($template, $params = [])
    {
        $output = parent::render($template, $params);
        switch ($template) {
            case 'model.php':
                // fix uses
                $tableName = $params['tableName'];
                if (array_key_exists($tableName, $this->relationUses) && $this->relationUses[$tableName]) {
                    $uses = array_unique($this->relationUses[$tableName]);
                    Helper::sortUses($uses);
                    $output = str_replace('use Yii;', 'use Yii;' . "\n" . 'use ' . implode(';' . "\n" . 'use ', $uses) . ';', $output);
                }
                // fix rules
                $output = preg_replace('~\'targetClass\' \=\> (\w+)Base\:\:className\(\)~', '\'targetClass\' => $1::className()', $output);
                // fix relations
                $nsClassName = $this->ns . '\\' . $params['className'];
                if (class_exists($nsClassName) && is_subclass_of($nsClassName, 'yii\boost\db\ActiveRecord')) {
                    $model = new $nsClassName;
                    $output = preg_replace_callback('~@return \\\\(yii\\\\db\\\\ActiveQuery)\s+\*/\s+public function ([^\(]+)\(\)~', function ($match) use ($model) {
                        if (method_exists($model, $match[2])) {
                            return str_replace($match[1], get_class(call_user_func([$model, $match[2]])) . '|\\' . $match[1], $match[0]);
                        } else {
                            return $match[0];
                        }
                    }, $output);
                }
                $params['relationUses'] = $this->relationUses;
                if (array_key_exists($tableName, $this->allRelations)) {
                    $params['allRelations'] = $this->allRelations[$tableName];
                } else {
                    $params['allRelations'] = [];
                }
                if (array_key_exists($tableName, $this->singularRelations)) {
                    $params['singularRelations'] = $this->singularRelations[$tableName];
                } else {
                    $params['singularRelations'] = [];
                }
                if (array_key_exists($tableName, $this->pluralRelations)) {
                    $params['pluralRelations'] = $this->pluralRelations[$tableName];
                } else {
                    $params['pluralRelations'] = [];
                }
                $output = preg_replace('~\}(\s*)$~', parent::render('model-part.php', $params) . '}$1', $output);
                break;
            case 'query.php':
                $code = <<<CODE
    /*public function active()
    {
        return \$this->andWhere('[[status]]=1');
    }*/

CODE;
                $output = str_replace($code, '', $output);
                $output = preg_replace('~\}(\s*)$~', parent::render('query-part.php', $params) . '}$1', $output);
                break;
        }
        $output = preg_replace_callback('~(@return |return new )\\\\((?:\w+\\\\)*\w+\\\\query)\\\\base\\\\(\w+Query)Base~', function ($match) {
            $nsClassName = $match[2] . '\\' . $match[3];
            if (class_exists($nsClassName)) {
                return $match[1] . '\\' . $nsClassName;
            } else {
                return $match[0];
            }
        }, $output);
        $output = preg_replace_callback('~(@see | @return |\[\[)\\\\((?:\w+\\\\)*\w+)\\\\base\\\\(\w+)Base~', function ($match) {
            $nsClassName = $match[2] . '\\' . $match[3];
            if (class_exists($nsClassName)) {
                return $match[1] . '\\' . $nsClassName;
            } else {
                return $match[0];
            }
        }, $output);
        return $output;
    }
}
