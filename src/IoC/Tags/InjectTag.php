<?php

namespace DC\IoC\Tags;

class InjectTag extends \phpDocumentor\Reflection\DocBlock\Tag {
    public static $name = 'inject';

    public function __toString()
    {
        return (string)$this->description;
    }
}