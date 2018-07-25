<?php

namespace yii\gii\plus\db;

use yii\db\ColumnSchema as BaseColumnSchema;
use yii\db\Schema;

/**
 * @property bool $isBoolean
 * @property bool $isInteger
 * @property bool $isNumber
 * @property bool $isDate
 * @property bool $isTime
 * @property bool $isDatetime
 * @property bool $hasDateFormat
 * @property string|null $dateFormat
 * @property bool $hasPattern
 * @property string|null $pattern
 */
class ColumnSchema extends BaseColumnSchema
{
    /**
     * @var bool
     */
    public $disableJsonSupport = false;

    /**
     * @var bool
     */
    public $isForeignKey;

    /**
     * @return bool
     */
    public function getIsBoolean()
    {
        return $this->type == Schema::TYPE_BOOLEAN;
    }

    /**
     * @return bool
     */
    public function getIsInteger()
    {
        return in_array($this->type, [
            Schema::TYPE_TINYINT,
            Schema::TYPE_SMALLINT,
            Schema::TYPE_INTEGER,
            Schema::TYPE_BIGINT
        ]);
    }

    /**
     * @return bool
     */
    public function getIsNumber()
    {
        return in_array($this->type, [
            Schema::TYPE_FLOAT,
            Schema::TYPE_DOUBLE,
            Schema::TYPE_DECIMAL,
            Schema::TYPE_MONEY
        ]);
    }

    /**
     * @return bool
     */
    public function getIsDate()
    {
        return $this->type == Schema::TYPE_DATE;
    }

    /**
     * @return bool
     */
    public function getIsTime()
    {
        return $this->type == Schema::TYPE_TIME;
    }

    /**
     * @return bool
     */
    public function getIsDatetime()
    {
        return in_array($this->type, [
            Schema::TYPE_DATETIME,
            Schema::TYPE_TIMESTAMP
        ]);
    }

    /**
     * @return bool
     */
    public function getHasDateFormat()
    {
        return $this->getIsDate() || $this->getIsTime() || $this->getIsDatetime();
    }

    /**
     * @return string|null
     */
    public function getDateFormat()
    {
        if ($this->getIsDate()) {
            return 'php:Y-m-d';
        } elseif ($this->getIsTime()) {
            return 'php:H:i:s';
        } elseif ($this->getIsDatetime()) {
            return 'php:Y-m-d H:i:s';
        }
        return null;
    }

    /**
     * @return bool
     */
    public function getHasPattern()
    {
        return in_array($this->type, [
            Schema::TYPE_DECIMAL,
            Schema::TYPE_MONEY
        ]);
    }

    /**
     * @return string|null
     */
    public function getPattern()
    {
        if (in_array($this->type, [Schema::TYPE_DECIMAL, Schema::TYPE_MONEY])) {
            $scale = $this->scale;
            $whole = $this->precision - $scale;
            $pattern = '~^';
            if (!$this->unsigned) {
                $pattern .= '\-?';
            }
            if ($whole > 0) {
                if ($whole == 1) {
                    $pattern .= '\d';
                } else {
                    $pattern .= '\d{1,' . $whole . '}';
                }
            } else {
                $pattern .= '0';
            }
            if ($scale > 0) {
                if ($scale == 1) {
                    $pattern .= '(?:\.\d)?';
                } else {
                    $pattern .= '(?:\.\d{1,' . $scale . '})?';
                }
            }
            $pattern .= '$~';
            return $pattern;
        }
        return null;
    }

    /**
     * @param TableSchema $table
     */
    public function fix(TableSchema $table)
    {
        if ($this->getIsInteger() && ($this->size == 1) && $this->unsigned) {
            $this->type = Schema::TYPE_BOOLEAN;
        }
        $this->isPrimaryKey = false;
        if (in_array($this->name, $table->primaryKey)) {
            $this->isPrimaryKey = true;
        }
        $this->isForeignKey = false;
        foreach ($table->foreignKeys as $foreignKey) {
            unset($foreignKey[0]);
            if (array_key_exists($this->name, $foreignKey)) {
                $this->isForeignKey = true;
                break;
            }
        }
    }
}
