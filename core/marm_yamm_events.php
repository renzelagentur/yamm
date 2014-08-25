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

    protected $_sConfigFile = 'marm_yamm.config.php';

    protected $_staticEntries = null;

    protected function activate()
    {
        if ( defined('MARM_YAMM_INFINITY_PREVENTION') )
            return;
        define('MARM_YAMM_INFINITY_PREVENTION', TRUE);
        include (getShopBasePath() . $this->_sConfigFile);
        $this->_staticEntries = $aYAMMConfig;
        $oModule = oxNew('oxModule');
        foreach ( $this->_staticEntries[marm_yamm_oxutilsobject::ENABLED] as $id )
        {
            $oModule->load($id);
            $oModule->activate();
        }
        // @formatter:off
        $toDisable = array_diff(
            $this->_staticEntries[marm_yamm_oxutilsobject::DISABLED],
            oxUtilsObject::getInstance()->getModuleVar('aDisabledModules'),
            $this->_staticEntries[marm_yamm_oxutilsobject::ENABLED]
        );
        // @formatter:on
        foreach ( $toDisable as $id )
        {
            $oModule->load($id);
            if ( $oModule->isActive() )
            {
                $oModule->deactivate();
            }
        }
    }

    protected function deactivate()
    {
        define('MARM_YAMM_TURNED_OFF', TRUE);
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = new marm_yamm_events();
        call_user_method_array($name, $instance, $arguments);
    }

}
