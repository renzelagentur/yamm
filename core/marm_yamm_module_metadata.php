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

class marm_yamm_module_metadata extends oxAdminDetails
{
    public function render()
    {
        if ( oxConfig::getParameter("moduleId") ) {
            $sModuleId = oxConfig::getParameter("moduleId");
        } else {
            $sModuleId = $this->getEditObjectId();
        }

        $oModule = oxNew('oxModule');

        if ( $sModuleId ) {
            if ( $oModule->load($sModuleId) ) {
                $this->_aViewData["oModule"] = $oModule;
                $this->_aViewData["sModuleName"] = basename($oModule->getInfo('title'));
                $this->_aViewData["sModuleId"] = str_replace("/", "_", $oModule->getModulePath());
            } else {
                oxRegistry::get("oxUtilsView")->addErrorToDisplay(new oxException('EXCEPTION_MODULE_NOT_LOADED'));
            }
        }

        parent::render();
        return 'marm_yamm_module_metadata.tpl';
    }

}
