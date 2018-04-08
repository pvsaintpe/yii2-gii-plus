<?php

namespace yii\gii\plus\db;

use yii\base\Object;

/**
 * @property int $count
 */
class PrimaryKeySchema extends Object
{

    /**
     * @var string[]
     */
    public $key = [];

    /**
     * @var bool
     */
    public $isForeignKey;

    /**
     * @var bool
     */
    public $isStatic;

    /**
     * @return int
     */
    public function getCount()
    {
        return count($this->key);
    }

    /**
     * @param TableSchema $table
     */
    public function fix(TableSchema $table)
    {
        $this->key = $table->primaryKey;
        $this->isForeignKey = false;
        foreach ($this->key as $columnName) {
            foreach ($table->foreignKeys as $foreignKey) {
                unset($foreignKey[0]);
                if (array_key_exists($columnName, $foreignKey)) {
                    $this->isForeignKey = true;
                    break;
                }
            }
            if ($this->isForeignKey) {
                break;
            }
        }
        $this->isStatic = false;
        if (!$this->isForeignKey && ($this->getCount() == 1)) {
            $column = $table->getColumn($this->key[0]);
            if ($column && $column->getIsInteger() && ($column->size == 3) && $column->unsigned) {
                $this->isStatic = !$column->autoIncrement;
            }
        }
    }
}
