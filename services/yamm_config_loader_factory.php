<?php

require_once __DIR__ . '/../services/yamm_config_loader_php.php';
require_once __DIR__ . '/../services/yamm_config_loader_csv.php';

/**
 * Class yamm_config_loader_factory
 *
 * Factory used for creation of config loader instances
 */
class yamm_config_loader_factory {

    private static $instances = array();

    public static function getLoader($loaderType = null)
    {
        if (is_null($loaderType)) {
            $loaderType = 'php';
        }

        if (!isset(self::$instances[$loaderType])) {
            switch (strtolower($loaderType)) {
                case "csv":
                        self::$instances[$loaderType] = new yamm_config_loader_csv();
                    break;
                case "php":
                   default:
                        self::$instances[$loaderType] = new yamm_config_loader_php();
                    break;
            }
        }

        return self::$instances[$loaderType];
    }
} 