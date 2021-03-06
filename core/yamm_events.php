<?php
/**
 * This file is part of a marmalade GmbH project
 *
 * It is Open Source and may be redistributed.
 * For contact information please visit http://www.marmalade.de
 *
 * Version:    1.0
 * Author URI: http://www.marmalade.de
 */

class yamm_events
{

    protected function activate()
    {
        define('YAMM_FORCE_RELOAD', TRUE);
        oxRegistry::getConfig()->saveShopConfVar('arr', 'aCachedConfig', null, null, 'yamm/yamm');
        oxRegistry::getConfig()->saveShopConfVar('num', 'iLastModified', 0, null, 'yamm/yamm');
        $oUtilsObject = oxNew('yamm_oxutilsobject');
        $oUtilsObject->getYAMMKeys();
    }

    protected function deactivate()
    {
        define('YAMM_TURNED_OFF', TRUE);
        oxRegistry::getConfig()->saveShopConfVar('arr', 'aCachedConfig', null, null, 'yamm/yamm');
        oxRegistry::getConfig()->saveShopConfVar('num', 'iLastModified', 0, null, 'yamm/yamm');
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = new yamm_events();
        call_user_method_array($name, $instance, $arguments);
    }

}
