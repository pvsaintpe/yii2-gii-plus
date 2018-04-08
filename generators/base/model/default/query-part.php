<?php

use yii\helpers\Inflector;

/* @var $this yii\web\View */
/* @var $generator yii\gii\plus\generators\base\model\Generator */
/* @var $tableName string */
/* @var $className string */
/* @var $queryClassName string */
/* @var $tableSchema yii\gii\plus\db\TableSchema */
/* @var $labels string[] */
/* @var $rules string[] */
/* @var $relations array */
/* @var $modelClassName string */

$methods = [];
$keyAttributes = [];

// deleted
$column = $tableSchema->getColumn('deleted');
if ($column && $column->getIsBoolean()) {
    $attribute = $column->name;
    $methodName = 'init';
    if (!in_array($methodName, $methods)) {
        $methods[] = $methodName;
        echo '
    public function init()
    {
        parent::init();
        $this->where(new \yii\boost\db\Expression(\'{a}.', $attribute, ' = 0 OR {a}.', $attribute, ' IS NULL\', [], [\'query\' => $this]));
    }
';
    }
}

// primary key
$primaryKey = $tableSchema->pk;
if ($primaryKey) {
    $keyAttributes = array_merge($keyAttributes, $primaryKey->key);
    if ($primaryKey->getCount() == 1) {
        $attribute = $primaryKey->key[0];
        $attributeArg = Inflector::variablize($attribute);
        $attributeType = $tableSchema->getColumn($attribute)->phpType;
        $methodName = 'pk';
        if (!in_array($methodName, $methods)) {
            $methods[] = $methodName;
            echo '
    /**
     * @param ', $attributeType, '|', $attributeType, '[] $', $attributeArg, '
     * @return $this
     */
    public function ', $methodName, '($', $attributeArg, ')
    {
        return $this->andWhere([$this->a(\'', $attribute, '\') => $', $attributeArg, ']);
    }
';
        }
        $methodName = $attributeArg;
        if (!in_array($methodName, $methods)) {
            $methods[] = $methodName;
            echo '
    /**
     * @param ', $attributeType, '|', $attributeType, '[] $', $attributeArg, '
     * @return $this
     */
    public function ', $methodName, '($', $attributeArg, ')
    {
        return $this->andWhere([$this->a(\'', $attribute, '\') => $', $attributeArg, ']);
    }
';
        }
    } else {
        $attributeArgs = [];
        $attributeTypes = [];
        foreach ($primaryKey->key as $i => $attribute) {
            $attributeArgs[$i] = Inflector::variablize($attribute);
            $attributeTypes[$i] = $tableSchema->getColumn($attribute)->phpType;
        }
        $methodName = 'pk';
        if (!in_array($methodName, $methods)) {
            $methods[] = $methodName;
            echo '
    /**
';
            foreach ($primaryKey->key as $i => $attribute) {
                echo '     * @param ', $attributeTypes[$i], '|', $attributeTypes[$i], '[] $', $attributeArgs[$i], '
';
            }
            echo '     * @return $this
     */
    public function ', $methodName, '($', implode(', $', $attributeArgs), ')
    {
        return $this->andWhere($this->a([
';
            foreach ($primaryKey->key as $i => $attribute) {
                $comma = ($i < $primaryKey->getCount() - 1) ? ',' : '';
                echo '            \'', $attribute, '\' => $', $attributeArgs[$i], $comma, '
';
            }
            echo '        ]));
    }
';
        }
        $methodName = Inflector::variablize(implode('_', $primaryKey->key));
        if (!in_array($methodName, $methods)) {
            $methods[] = $methodName;
            echo '
    /**
';
            foreach ($primaryKey->key as $i => $attribute) {
                echo '     * @param ', $attributeTypes[$i], '|', $attributeTypes[$i], '[] $', $attributeArgs[$i], '
';
            }
            echo '     * @return $this
     */
    public function ', $methodName, '($', implode(', $', $attributeArgs), ')
    {
        return $this->andWhere($this->a([
';
            foreach ($primaryKey->key as $i => $attribute) {
                $comma = ($i < $primaryKey->getCount() - 1) ? ',' : '';
                echo '            \'', $attribute, '\' => $', $attributeArgs[$i], $comma, '
';
            }
            echo '        ]));
    }
';
        }
    }
}

// foreign keys
foreach ($tableSchema->fks as $foreignKey) {
    $keyAttributes = array_merge($keyAttributes, $foreignKey->key);
    if ($foreignKey->getCount() == 1) {
        $attribute = $foreignKey->key[0];
        $attributeArg = Inflector::variablize($attribute);
        $attributeType = $tableSchema->getColumn($attribute)->phpType;
        $methodName = $attributeArg;
        if (!in_array($methodName, $methods)) {
            $methods[] = $methodName;
            echo '
    /**
     * @param ', $attributeType, '|', $attributeType, '[] $', $attributeArg, '
     * @return $this
     */
    public function ', $methodName, '($', $attributeArg, ')
    {
        return $this->andWhere([$this->a(\'', $attribute, '\') => $', $attributeArg, ']);
    }
';
        }
    } else {
        $attributeArgs = [];
        $attributeTypes = [];
        foreach ($foreignKey->key as $i => $attribute) {
            $attributeArgs[$i] = Inflector::variablize($attribute);
            $attributeTypes[$i] = $tableSchema->getColumn($attribute)->phpType;
        }
        $methodName = Inflector::variablize(implode('_', $foreignKey->key));
        if (!in_array($methodName, $methods)) {
            $methods[] = $methodName;
            echo '
    /**
';
            foreach ($foreignKey->key as $i => $attribute) {
                echo '     * @param ', $attributeTypes[$i], '|', $attributeTypes[$i], '[] $', $attributeArgs[$i], '
';
            }
            echo '     * @return $this
     */
    public function ', $methodName, '($', implode(', $', $attributeArgs), ')
    {
        return $this->andWhere($this->a([
';
            foreach ($foreignKey->key as $i => $attribute) {
                $comma = ($i < $foreignKey->getCount() - 1) ? ',' : '';
                echo '            \'', $attribute, '\' => $', $attributeArgs[$i], $comma, '
';
            }
            echo '        ]));
    }
';
        }
    }
}

// unique keys
foreach ($tableSchema->uks as $uniqueKey) {
    $keyAttributes = array_merge($keyAttributes, $uniqueKey->key);
    if ($uniqueKey->getCount() == 1) {
        $attribute = $uniqueKey->key[0];
        $attributeArg = Inflector::variablize($attribute);
        $attributeType = $tableSchema->getColumn($attribute)->phpType;
        $methodName = $attributeArg;
        if (!in_array($methodName, $methods)) {
            $methods[] = $methodName;
            echo '
    /**
     * @param ', $attributeType, '|', $attributeType, '[] $', $attributeArg, '
     * @return $this
     */
    public function ', $methodName, '($', $attributeArg, ')
    {
        return $this->andWhere([$this->a(\'', $attribute, '\') => $', $attributeArg, ']);
    }
';
        }
    } else {
        $attributeArgs = [];
        $attributeTypes = [];
        foreach ($uniqueKey->key as $i => $attribute) {
            $attributeArgs[$i] = Inflector::variablize($attribute);
            $attributeTypes[$i] = $tableSchema->getColumn($attribute)->phpType;
        }
        $methodName = Inflector::variablize(implode('_', $uniqueKey->key));
        if (!in_array($methodName, $methods)) {
            $methods[] = $methodName;
            echo '
    /**
';
            foreach ($uniqueKey->key as $i => $attribute) {
                echo '     * @param ', $attributeTypes[$i], '|', $attributeTypes[$i], '[] $', $attributeArgs[$i], '
';
            }
            echo '     * @return $this
     */
    public function ', $methodName, '($', implode(', $', $attributeArgs), ')
    {
        return $this->andWhere($this->a([
';
            foreach ($uniqueKey->key as $i => $attribute) {
                $comma = ($i < $uniqueKey->getCount() - 1) ? ',' : '';
                echo '            \'', $attribute, '\' => $', $attributeArgs[$i], $comma, '
';
            }
            echo '        ]));
    }
';
        }
    }
}

// primary/foreign/unique keys
foreach ($keyAttributes as $attribute) {
    $attributeArg = Inflector::variablize($attribute);
    $attributeType = $tableSchema->getColumn($attribute)->phpType;
    $methodName = $attributeArg;
    if (!in_array($methodName, $methods)) {
        $methods[] = $methodName;
        echo '
    /**
     * @param ', $attributeType, '|', $attributeType, '[] $', $attributeArg, '
     * @return $this
     */
    public function ', $methodName, '($', $attributeArg, ')
    {
        return $this->andWhere([$this->a(\'', $attribute, '\') => $', $attributeArg, ']);
    }
';
    }
}

// boolean
foreach ($tableSchema->columns as $column) {
    if ($column->getIsBoolean()) {
        $attribute = $column->name;
        $attributeArg = Inflector::variablize($attribute);
        $methodName = $attributeArg;
        if (!in_array($methodName, $methods)) {
            $methods[] = $methodName;
            echo '
    /**
     * @param int|bool $', $attributeArg, '
     * @return $this
     */
    public function ', $methodName, '($', $attributeArg, ' = true)
    {
        return $this->andWhere([$this->a(\'', $attribute, '\') => $', $attributeArg, ' ? 1 : 0]);
    }
';
        }
    }
}

// ...expires_at
foreach ($tableSchema->columns as $column) {
    $attribute = $column->name;
    if (preg_match('~(?:^|_)expires_at$~', $attribute) && ($column->getIsDate() || $column->getIsDatetime())) {
        $attributeArg = Inflector::variablize(str_replace('expires_at', 'not_expired', $attribute));
        $methodName = $attributeArg;
        if (!in_array($methodName, $methods)) {
            $methods[] = $methodName;
            echo '
    /**
     * @param bool $', $attributeArg, '
     * @return $this
     */
    public function ', $methodName, '($', $attributeArg, ' = true)
    {
';
            $functionName = $column->getIsDate() ? 'CURDATE' : 'NOW';
            if ($column->allowNull) {
                echo '        $columnName = $this->a(\'', $attribute, '\');
        if ($', $attributeArg, ') {
            return $this->andWhere($columnName . \' IS NULL OR \' . $columnName . \' > ', $functionName, '()\');
        } else {
            return $this->andWhere($columnName . \' IS NOT NULL AND \' . $columnName . \' <= ', $functionName, '()\');
        }
';
            } else {
                echo '        if ($', $attributeArg, ') {
            return $this->andWhere($this->a(\'', $attribute, '\') . \' > ', $functionName, '()\');
        } else {
            return $this->andWhere($this->a(\'', $attribute, '\') . \' <= ', $functionName, '()\');
        }
';
            }
            echo '    }
';
        }
    }
}
