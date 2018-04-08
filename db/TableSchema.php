<?php

namespace yii\gii\plus\db;

use yii\db\TableSchema as BaseTableSchema;

/**
 * @property ColumnSchema[] $columns
 * @method ColumnSchema getColumn(string $name)
 */
class TableSchema extends BaseTableSchema
{

    /**
     * @var string
     */
    public $comment;

    /**
     * @var bool
     */
    public $isView;

    /**
     * @var bool
     */
    public $isStatic;

    /**
     * @var array
     */
    public $uniqueKeys = [];

    /**
     * @var string[]
     */
    public $titleKey = [];

    /**
     * @var PrimaryKeySchema
     */
    public $pk;

    /**
     * @var ForeignKeySchema[]
     */
    public $fks = [];

    /**
     * @var UniqueKeySchema[]
     */
    public $uks = [];

    /**
     * @var TitleKeySchema
     */
    public $tk;

    /**
     * @return PrimaryKeySchema
     */
    public function getPrimaryKey()
    {
        return $this->pk;
    }

    /**
     * @return ForeignKeySchema|null
     */
    public function getForeignKey(array $key)
    {
        $key = array_values($key);
        foreach ($this->fks as $fk) {
            if (!array_diff($fk->key, $key) && !array_diff($key, $fk->key)) {
                return $fk;
            }
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasForeignKey(array $key)
    {
        return (bool)$this->getForeignKey($key);
    }

    /**
     * @return UniqueKeySchema|null
     */
    public function getUniqueKey(array $key)
    {
        foreach ($this->uks as $uk) {
            if (!array_diff($uk->key, $key) && !array_diff($key, $uk->key)) {
                return $uk;
            }
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasUniqueKey(array $key)
    {
        return (bool)$this->getUniqueKey($key);
    }

    /**
     * @return TitleKeySchema
     */
    public function getTitleKey()
    {
        return $this->tk;
    }

    public function fix()
    {
        foreach ($this->columns as $column) {
            $column->fix($this);
        }
        if (count($this->primaryKey)) {
            $this->pk = new PrimaryKeySchema;
            $this->pk->fix($this);
            $this->isStatic = $this->pk->isStatic;
        }
        $this->fks = [];
        foreach ($this->foreignKeys as $foreignKey) {
            $fk = new ForeignKeySchema;
            $fk->fix($this, $foreignKey);
            $this->fks[] = $fk;
        }
        $this->uks = [];
        foreach ($this->uniqueKeys as $uniqueKey) {
            $uk = new UniqueKeySchema;
            $uk->fix($this, $uniqueKey);
            $this->uks[] = $uk;
        }
        if (count($this->titleKey)) {
            $this->tk = new TitleKeySchema;
            $this->tk->fix($this);
        }
    }
}
