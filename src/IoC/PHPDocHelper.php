<?php

namespace DC\IoC;

class PHPDocHelper {
    static function getDocumentedTypes(\ReflectionFunctionAbstract $method) {
        $phpdoc = new \phpDocumentor\Reflection\DocBlock($method);
        $params = $phpdoc->getTagsByName("param");
        $results = [];

        foreach ($params as $param) {
            $type = $param->getType();
            if ($type == "") {
                if (preg_match('/^(?:array\|)?(\S+)(?:array\|)?/m', $param->getDescription(), $results)) {
                    $type = $results[1];
                }
            }
            $results[$param->getVariableName()] = $type;
        }
        return $results;
    }

    static function getDocumentedTypeFromProperty(\ReflectionProperty $property) {
        $phpdoc = new \phpDocumentor\Reflection\DocBlock($property);
        $tags = $phpdoc->getTagsByName("var");
        if (count($tags) == 1) {
            /** @var \phpDocumentor\Reflection\DocBlock\Tag\VarTag $tag */
            $tag = reset($tags);
            return $tag->getType();
        }
        return null;
    }

    static function isArrayType($type) {
        return $type[strlen($type) - 2] == '[' && $type[strlen($type) - 1] == ']';
    }

    static function removeArray($type)
    {
        return trim($type, '[]');
    }
}