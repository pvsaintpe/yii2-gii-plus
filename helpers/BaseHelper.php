<?php

namespace yii\gii\plus\helpers;

use yii\db\Connection;
use yii\base\NotSupportedException;
use Yii;

class BaseHelper
{

    /**
     * @var Connection[]
     */
    protected static $dbConnections;

    /**
     * @return Connection[]
     */
    public static function getDbConnections()
    {
        if (is_null(static::$dbConnections)) {
            static::$dbConnections = [];
            foreach (Yii::$app->getComponents() as $id => $definition) {
                if (is_array($definition)
                    && array_key_exists('class', $definition)
                    && class_exists($definition['class'])
                    && (($definition['class'] == 'yii\db\Connection')
                        || is_subclass_of($definition['class'], 'yii\db\Connection')
                    )
                ) {
                    $db = Yii::$app->get($id);
                    if ($db instanceof Connection) {
                        static::$dbConnections[$id] = $db;
                    }
                }
            }
        }
        return static::$dbConnections;
    }

    /**
     * @param Connection $db
     * @param bool $refresh
     * @return string[]
     */
    public static function getSchemaNames(Connection $db, $refresh = false)
    {
        try {
            $schemaNames = array_diff($db->getSchema()->getSchemaNames($refresh), ['public']);
        } catch (NotSupportedException $e) {
            $schemaNames = [];
        }
        return $schemaNames;
    }

    /**
     * @var string[]
     */
    protected static $modelNamespaces;

    /**
     * @return string[]
     */
    public static function getModelNamespaces()
    {
        if (is_null(static::$modelNamespaces)) {
            static::$modelNamespaces = [];
            foreach (['app', 'backend', 'common', 'console', 'frontend'] as $appNs) {
                $appPath = Yii::getAlias('@' . $appNs, false);
                if ($appPath) {
                    static::$modelNamespaces[] = $appNs . '\models';
                }
            }
        }
        return static::$modelNamespaces;
    }

    /**
     * @var string[]
     */
    protected static $modelDeepNamespaces;

    /**
     * @return string[]
     */
    public static function getModelDeepNamespaces()
    {
        if (is_null(static::$modelDeepNamespaces)) {
            static::$modelDeepNamespaces = [];
            foreach (static::getModelNamespaces() as $modelNs) {
                static::$modelDeepNamespaces[] = $modelNs;
                static::$modelDeepNamespaces = array_merge(
                    static::$modelDeepNamespaces,
                    static::getModelSubNamespaces($modelNs)
                );
            }
        }
        return static::$modelDeepNamespaces;
    }

    /**
     * @param string $modelNs
     * @return string[]
     */
    protected static function getModelSubNamespaces($modelNs)
    {
        $modelSubNamespaces = [];
        foreach (glob(Yii::getAlias('@' . str_replace('\\', '/', $modelNs)) . '/*', GLOB_ONLYDIR) as $path) {
            $basename = basename($path);
            if (($basename != 'base') && ($basename != 'query') && ($basename != 'search')) {
                $modelSubNs = $modelNs . '\\' . $basename;
                $modelSubNamespaces[] = $modelSubNs;
                $modelSubNamespaces = array_merge($modelSubNamespaces, static::getModelSubNamespaces($modelSubNs));
            }
        }
        return $modelSubNamespaces;
    }

    /**
     * @var string[]
     */
    protected static $modelClasses;

    /**
     * @return string[]
     */
    public static function getModelClasses()
    {
        if (is_null(static::$modelClasses)) {
            static::$modelClasses = [];
            foreach (static::getModelDeepNamespaces() as $modelNs) {
                foreach (glob(Yii::getAlias('@' . str_replace('\\', '/', $modelNs)) . '/*.php') as $modelPath) {
                    $modelClass = $modelNs . '\\' . basename($modelPath, '.php');
                    if (class_exists($modelClass)
                        && is_subclass_of($modelClass, 'yii\boost\db\ActiveRecord')
                        && !in_array('search', get_class_methods($modelClass))
                    ) {
                        static::$modelClasses[] = $modelClass;
                    }
                }
            }
        }
        return static::$modelClasses;
    }

    /**
     * @var array
     */
    protected static $modelClassTableNameMap;

    /**
     * @return array
     */
    public static function getModelClassTableNameMap()
    {
        if (is_null(static::$modelClassTableNameMap)) {
            static::$modelClassTableNameMap = [];
            /* @var $modelClass string|\yii\boost\db\ActiveRecord */
            foreach (static::getModelClasses() as $modelClass) {
                static::$modelClassTableNameMap[$modelClass] = $modelClass::tableName();
            }
        }
        return static::$modelClassTableNameMap;
    }

    /**
     * @param string $tableName
     * @return string|false
     */
    public static function getModelClassByTableName($tableName)
    {
        return array_search($tableName, static::getModelClassTableNameMap());
    }

    /**
     * @param string[] $uses
     * @return bool
     */
    public static function sortUses(array &$uses)
    {
        return usort($uses, function ($use1, $use2) {
            if (preg_match('~[\\\\\s]([^\\\\\s]+)$~', $use1, $match)) {
                $use1 = $match[1];
            }
            if (preg_match('~[\\\\\s]([^\\\\\s]+)$~', $use2, $match)) {
                $use2 = $match[1];
            }
            return strcasecmp($use1, $use2);
        });
    }

    /**
     * @param string[] $pieces
     * @param int $multiplier
     * @return string
     */
    public static function implode(array $pieces, $multiplier)
    {
        if (count($pieces)) {
            $pieces = array_unique($pieces);
            if (count($pieces) == 1) {
                return '[\'' . $pieces[0] . '\']';
            } else {
                $glue = '\',' . "\n" . str_repeat('    ', $multiplier + 1) . '\'';
                return '[' . "\n" .
                str_repeat('    ', $multiplier + 1) . '\'' . implode($glue, $pieces) . '\'' . "\n" .
                str_repeat('    ', $multiplier) . ']';
            }
        } else {
            return '[]';
        }
    }
}
