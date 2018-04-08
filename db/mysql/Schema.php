<?php

namespace yii\gii\plus\db\mysql;

use yii\gii\plus\db\ColumnSchema;
use yii\db\mysql\Schema as MysqlSchema;
use yii\base\NotSupportedException;
use PDO;
use yii\gii\plus\db\TableSchema;

class Schema extends MysqlSchema
{

    /**
     * @inheritdoc
     * @return TableSchema
     */
    protected function loadTableSchema($name)
    {
        $table = parent::loadTableSchema($name);
        if (is_object($table)) {
            $table = new TableSchema(get_object_vars($table));
            $this->findComment($table);
            $this->findIsView($table);
            $this->findUniqueKeys($table);
            $this->findTitleKey($table);
            $table->fix();
        }
        return $table;
    }

    /**
     * @inheritdoc
     * @return ColumnSchema
     */
    protected function loadColumnSchema($info)
    {
        $column = parent::loadColumnSchema($info);
        if (is_object($column)) {
            $column = new ColumnSchema(get_object_vars($column));
        }
        return $column;
    }

    /**
     * @inheritdoc
     */
    protected function findColumns($table)
    {
        if (parent::findColumns($table)) {
            if (!count($table->primaryKey)) {
                foreach ($table->columns as $column) {
                    if (preg_match('~^pk_~', $column->name)) {
                        $table->primaryKey[] = $column->name;
                    }
                }
                if (!count($table->primaryKey)) {
                    foreach ($table->columns as $column) {
                        $table->primaryKey[] = $column->name;
                        break;
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function findConstraints($table)
    {
        parent::findConstraints($table);
        if (!count($table->foreignKeys)) {
            foreach ($table->columns as $column) {
                if (preg_match('~^(?:pk_|fk_|uk_|tk_)?(\w+)_id$~', $column->name, $match) && $this->getTableSchema($match[1])) {
                    $table->foreignKeys[] = [$match[1], $match[0] => 'id'];
                }
            }
        }
    }

    /**
     * @param TableSchema $table
     */
    protected function findComment(TableSchema $table)
    {
        $sql = 'SHOW CREATE TABLE ' . $this->db->quoteTableName($table->fullName);
        $row = $this->db->createCommand($sql)->queryOne(PDO::FETCH_NUM);
        if (is_array($row) && (count($row) == 2) && preg_match('~\)([^\)]*)$~', $row[1], $match)) {
            $tableOptions = $match[1];
            if (preg_match('~COMMENT\s*\=?\s*\'([^\']+)\'~', $tableOptions, $match)) {
                $table->comment = $match[1];
            }
        }
    }

    /**
     * @param TableSchema $table
     */
    protected function findIsView(TableSchema $table)
    {
        $sql = 'SHOW CREATE TABLE ' . $this->db->quoteTableName($table->fullName);
        $row = $this->db->createCommand($sql)->queryOne();
        if (is_array($row)) {
            $table->isView = array_key_exists('View', $row);
        }
    }

    /**
     * @param TableSchema $table
     */
    protected function findUniqueKeys(TableSchema $table)
    {
        try {
            $table->uniqueKeys = $this->findUniqueIndexes($table);
        } catch (NotSupportedException $e) {
            // do nothing
        }
        if (!count($table->uniqueKeys)) {
            $uniqueKey = [];
            foreach ($table->columns as $column) {
                if (preg_match('~^uk_~', $column->name)) {
                    $uniqueKey[] = $column->name;
                }
            }
            if (count($uniqueKey)) {
                $table->uniqueKeys[] = $uniqueKey;
            }
        }
    }

    /**
     * @param TableSchema $table
     */
    protected function findTitleKey(TableSchema $table)
    {
        foreach ($table->uniqueKeys as $uniqueKey) {
            $types = [];
            foreach ($uniqueKey as $columnName) {
                $types[] = $table->getColumn($columnName)->type;
            }
            if (in_array(Schema::TYPE_CHAR, $types) || in_array(Schema::TYPE_STRING, $types)) {
                $table->titleKey = $uniqueKey;
                break;
            }
        }
        if (!count($table->titleKey)) {
            foreach ($table->columns as $column) {
                if (preg_match('~^tk_~', $column->name)) {
                    $table->titleKey[] = $column->name;
                }
            }
            if (!count($table->titleKey)) {
                $table->titleKey = $table->primaryKey;
            }
        }
    }
}
