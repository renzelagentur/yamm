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


class marm_yamm_oxmodule extends marm_yamm_oxmodule_parent
{

    public function isEnabledByYAMM()
    {
        return in_array($this->getId(), oxUtilsObject::getInstance()->getModuleVar(marm_yamm_oxutilsobject::ENABLED));
    }

    public function isDisabledByYAMM()
    {
        return in_array($this->getId(), oxUtilsObject::getInstance()->getModuleVar(marm_yamm_oxutilsobject::DISABLED));
    }
    
    public function hasX($x)
    {
        return isset($this->_aModule[$x]) && !empty($this->_aModule[$x]);
    }
    
    public function getMetadata()
    {
        return $this->_aModule;
    }

}
