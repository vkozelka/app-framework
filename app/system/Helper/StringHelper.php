<?php

namespace App\System\Helper;

class StringHelper
{

    public static function kebabToCamelCase(string $subject): string
    {
        return str_replace(" ", "", ucwords(str_replace("_", " ", $subject)));
    }

    public static function stringToClass(string $subject): string
    {
        str_replace(" ", "\\", ucwords(str_replace(".", " ", $subject)));
    }

}