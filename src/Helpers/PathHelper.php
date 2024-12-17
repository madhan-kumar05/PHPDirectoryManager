<?php

namespace DirectoryManager\Helpers;

class PathHelper
{
    public static function joinPaths(string ...$segments): string
    {
        return implode(DIRECTORY_SEPARATOR, array_filter($segments));
    }
}
