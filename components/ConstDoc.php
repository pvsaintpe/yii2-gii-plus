<?php

namespace pvsaintpe\gii\plus\components;

/**
 * Simple DocComment support for class constants.
 * http://stackoverflow.com/questions/22103019/php-reflection-get-constants-doc-comment
 */
class ConstDoc
{
    /**
     * @var array Constant names to DocComment strings.
     */
    private $docComments = [];

    /**
     * Constructor.
     */
    public function __construct($clazz)
    {
        $reflection = new \ReflectionClass($clazz);
        $content = file_get_contents($reflection->getFileName());
        $tokens = token_get_all($content);
        $this->docComments = $this->parse($tokens);
    }

    /**
     * Parses the class for constant DocComments.
     * @param $tokens
     * @return array
     */
    public static function parse($tokens)
    {
        $docComments = [];
        $doc = null;
        $isConst = false;
        foreach ($tokens as $token) {
            if (!is_array($token) || (count($token) <= 1)) {
                continue;
            }

            list($tokenType, $tokenValue) = $token;

            switch ($tokenType) {
                // ignored tokens
                case T_WHITESPACE:
                case T_COMMENT:
                    break;

                case T_DOC_COMMENT:
                    $doc = $tokenValue;
                    break;

                case T_CONST:
                    $isConst = true;
                    break;

                case T_STRING:
                    if ($isConst) {
                        $docComments[$tokenValue] = static::clean($doc);
                    }
                    $doc = null;
                    $isConst = false;
                    break;

                // all other tokens reset the parser
                default:
                    $doc = null;
                    $isConst = false;
                    break;
            }
        }
        return $docComments;
    }

    /** Returns an array of all constants to their DocComment. If no comment is present the comment is null. */
    public function getDocComments()
    {
        return $this->docComments;
    }

    /**
     * Returns the DocComment of a class constant.
     * Null if the constant has no DocComment or the constant does not exist.
     * @param string $constantName
     * @return string|null
     */
    public function getDocComment($constantName)
    {
        if (!isset($this->docComments)) {
            return null;
        }

        return $this->docComments[$constantName];
    }

    /** Cleans the doc comment. Returns null if the doc comment is null. */
    private static function clean($doc)
    {
        if ($doc === null) {
            return null;
        }

        $comment = null;
        $params = null;
        $lines = preg_split('/\n/', $doc);
        foreach ($lines as $line) {
            if (preg_match('~\*\s*@([a-zA-Z]+)\s*([a-zA-Z-]*)~', $line, $match)) {
                $name = $match[1];
                $value = $match[2];
                $params[$name] = $value;
                continue;
            }

            $line = trim($line, "/* \t\x0B\0\r");
            if ($line === '') {
                continue;
            }

            $comment .= ($comment ? ' ' : '') . $line;
        }

        return compact('comment', 'params');
    }
}
