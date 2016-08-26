<?php
namespace Autohome;

class Config
{
    private static $instance;

    private function __construct($filePath)
    {
        if(!$filePath) {
            throw new \Exception('Configuration file not provided');
        }
        if(!file_exists($filePath)) {
            throw new \Exception('Configuration file not found');
        }

    }

    public static function load($filePath=null)
    {
        return self::$instance ?: new self($filePath);
    }

    // Retourne la valeur d'une variable de configuration
    public static function get($variable, $default=null)
    {
        return $default;
    }
}
