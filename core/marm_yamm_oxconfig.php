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


class marm_yamm_oxconfig extends marm_yamm_oxconfig_parent
{
    protected $_sConfigFile = 'marm_yamm.config.php';

    protected $_staticEntries = null;

    public function init()
    {
        if ( $this->_blInit )
        {
            return;
        }
        oxUtilsObject::getInstance()->init();
        parent::init();
        foreach ( oxUtilsObject::getInstance()->getYAMMKeys() as $name )
        {
            $this->_aConfigParams[$name] = oxUtilsObject::getInstance()->getModuleVar($name);
        }
    }

}
