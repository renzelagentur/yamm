<?php

/**
 * Class used to fetch module vars from any shop
 *
 * @package services
 */
class yamm_module_vars {

    private $_oDb;

    public function __construct(\oxLegacyDb $oDb) {
        $this->_oDb = $oDb;
    }

    public function getModules($iShopId) {
        $sQuery = sprintf('SELECT OXID, OXSHOPID, OXVARNAME, OXVARTYPE, DECODE(oxvarvalue, "%s") as OXVARVALUE FROM oxconfig WHERE OXVARNAME = "aModules" AND OXSHOPID = %s', oxRegistry::getConfig()->getConfigParam('sConfigKey'), (int) $iShopId);

        $result = $this->_oDb->getAll($sQuery);
        $varValue = unserialize($result[0][4]);
        return $varValue;
    }

    public function getModulePaths($iShopId) {
        $sQuery = sprintf('SELECT OXID, OXSHOPID, OXVARNAME, OXVARTYPE, DECODE(oxvarvalue, "%s") as OXVARVALUE FROM oxconfig WHERE OXVARNAME = "aModulePaths" AND OXSHOPID = %d', oxRegistry::getConfig()->getConfigParam('sConfigKey'), (int) $iShopId);
        $result = $this->_oDb->getAll($sQuery);
        $varValue = unserialize($result[0][4]);
        return $varValue;

    }

    public function getDisabledModules($iShopId) {
        $sQuery = sprintf('SELECT OXID, OXSHOPID, OXVARNAME, OXVARTYPE, DECODE(oxvarvalue, "%s") as OXVARVALUE FROM oxconfig WHERE OXVARNAME = "aDisabledModules" AND OXSHOPID = %s', oxRegistry::getConfig()->getConfigParam('sConfigKey'), (int) $iShopId);

        $result = $this->_oDb->getAll($sQuery);
        $varValue = unserialize($result[0][4]);
        return $varValue;
    }

}