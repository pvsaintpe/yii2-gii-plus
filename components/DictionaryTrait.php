<?php

namespace pvsaintpe\gii\plus\components;

use Yii;

/**
 * Trait DictionaryTrait
 * @package pvsaintpe\gii\plus\components
 */
trait DictionaryTrait
{
    /**
     * @param array $params
     * @return array
     */
    public static function getMessages($params = [])
    {
        $messages = [];
        $className = get_called_class();
        $doc = new ConstDoc($className);
        $comments = $doc->getDocComments();
        foreach ($comments as $constName => $comment) {
            $code = constant(static::class . '::' . $constName);
            if (!empty($comment['comment'])) {
                $category = $comment['params']['message'] ?: 'message';
                $messages[$code] = Yii::t($category, $comment['comment'], $params);
            }
        }
        return $messages;
    }

    /**
     * @param string $code
     * @param array $params
     * @return string|null
     */
    public static function getMessage($code, $params = [])
    {
        return static::getMessages($params)[$code] ?? null;
    }

    /**
     * Возвращает имя из phpDoc
     * @return string|null
     */
    public function getDocName()
    {
        return static::getMessage($this->id);
    }

    /**
     * @param $code
     * @return mixed
     */
    public static function getIdByCode($code)
    {
        return array_search($code, self::getConstants());
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function getCodeById($id)
    {
        return self::getConstants()[$id] ?? null;
    }

    /**
     * @param $code
     * @return string|null
     */
    public static function getMessageByCode($code)
    {
        return static::getMessage(static::getIdByCode($code));
    }
}
