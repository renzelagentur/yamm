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
