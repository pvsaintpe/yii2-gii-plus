<?php

namespace yii\gii\plus\db;

use yii\base\Object;

/**
 * @property int $count
 */
class ForeignKeySchema extends Object
{

    /**
     * @var string
     */
    public $tableName;

    /**
     * @var array
     */
    public $link = [];

    /**
     * @var string[]
     */
    public $key = [];

    /**
     * @var string[]
     */
    public $inTableKey = [];

    /**
     * @var bool
     */
    public $isPrimaryKey;

    /**
     * @return int
     */
    public function getCount()
    {
        return count($this->key);
    }

    /**
     * @param TableSchema $table
     * @param array $key
     */
    public function fix(TableSchema $table, array $key)
    {
        $this->tableName = $key[0];
        unset($key[0]);
        $this->link = $key;
        $this->key = array_keys($this->link);
        $this->inTableKey = array_values($this->link);
        $this->isPrimaryKey = false;
        foreach ($this->key as $columnName) {
            if (in_array($columnName, $table->primaryKey)) {
                $this->isPrimaryKey = true;
                break;
            }
        }
    }
}
