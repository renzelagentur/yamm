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


class yamm_oxmodule extends yamm_oxmodule_parent
{

    public function isEnabledByYAMM()
    {
        if (is_array(oxUtilsObject::getInstance()->getModuleVar(yamm_oxutilsobject::ENABLED))) {
            return in_array($this->getId(), oxUtilsObject::getInstance()->getModuleVar(yamm_oxutilsobject::ENABLED));
        }
        return false;
    }

    public function isDisabledByYAMM()
    {
        if (is_array(oxUtilsObject::getInstance()->getModuleVar(yamm_oxutilsobject::ENABLED))) {
            return in_array($this->getId(), oxUtilsObject::getInstance()->getModuleVar(yamm_oxutilsobject::DISABLED));
        }
        return false;
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
