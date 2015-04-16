<?php
/**
 * This file is part of a yammalade GmbH project
 *
 * It is Open Source and may be redistributed.
 * For contact information please visit http://www.yammalade.de
 *
 * Version:    1.0
 * Author URI: http://www.yammalade.de
 */

class yamm_oxconfig extends yamm_oxconfig_parent
{
    public function init()
    {
        oxUtilsObject::getInstance()->initYAMM();
        parent::init();
    }

    public function getConfigParam($sName)
    {
        $mValue = parent::getConfigParam($sName);
        $oUtils = oxUtilsObject::getInstance();
        if ( $oUtils->hasYAMMKey($sName) ) {
            $mValue = $oUtils->getModuleVar($sName);
        }
        return $mValue;
    }

}
