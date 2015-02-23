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

class marm_yamm_events
{

    protected function activate()
    {
        define('MARM_YAMM_FORCE_RELOAD', TRUE);
        oxRegistry::getConfig()->saveShopConfVar('arr', 'aCachedConfig', null, null, 'marm/yamm');
        oxRegistry::getConfig()->saveShopConfVar('num', 'iLastModified', 0, null, 'marm/yamm');
        $oUtilsObject = oxNew('marm_yamm_oxutilsobject');
        $oUtilsObject->initYAMM();
    }

    protected function deactivate()
    {
        define('MARM_YAMM_TURNED_OFF', TRUE);
        oxRegistry::getConfig()->saveShopConfVar('arr', 'aCachedConfig', null, null, 'marm/yamm');
        oxRegistry::getConfig()->saveShopConfVar('num', 'iLastModified', 0, null, 'marm/yamm');
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = new marm_yamm_events();
        call_user_method_array($name, $instance, $arguments);
    }

}
