<?php

namespace OJSXml;

class Config
{
    private static $data;

    public static function load($configFile)
    {
        self::$data = parse_ini_file($configFile);
    }

    public static function get($key)
    {
        return self::$data[$key];
    }
}
