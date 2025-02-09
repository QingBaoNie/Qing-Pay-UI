<?php
class EnvLoader {
    private static $config = [];
    private static $isLoaded = false;

    private static function autoload() {
        if (!self::$isLoaded) {
            self::load();
            self::$isLoaded = true;
        }
    }

    public static function load($path = null) {
        if($path === null) {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/.env';
        }
        
        if(file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach($lines as $line) {
                if(strpos($line, '=') !== false && strpos(trim($line), '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    self::$config[trim($key)] = trim($value);
                }
            }
        }
    }

    public static function get($key, $default = null) {
        self::autoload();
        return self::$config[$key] ?? $default;
    }
}
