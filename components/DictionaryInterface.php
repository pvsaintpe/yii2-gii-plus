<?php

namespace pvsaintpe\gii\plus\components;

interface DictionaryInterface
{
    /**
     * @return string|null
     */
    public function getDocName();

    /**
     * @return array
     */
    public static function getConstants();
}
