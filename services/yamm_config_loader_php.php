<?php

require_once __DIR__ .'/yamm_config_loader_interface.php';

/**
 * Class yamm_config_loader_php
 *
 * Config Loader that loads the config from a PHP File
 */
class yamm_config_loader_php implements yamm_config_loader_interface {

    private $_staticEntries = array();

    private $_sConfigPath = null;

    /**
     * Returns the path of the config file to use
     * @return null|string
     */
    private function getConfigPath() {
        if ($this->_sConfigPath === null) {
            $sConfigPath = rtrim(getShopBasePath(), '/') . '/YAMM';
            if (defined('YAMM_CONTEXT') && YAMM_CONTEXT !== null) {
                $sYAMMContext = YAMM_CONTEXT;
            } else {
                $sYAMMContext = 'production';
            }

            if (is_dir($sConfigPath . '/' . $sYAMMContext)) {
                $sConfigPath .= '/' . $sYAMMContext;
                // If there is a config for a production context, fall back to it, otherwise use none at all
            } else if (is_dir($sConfigPath . '/production')) {
                $sConfigPath .= '/production';
            }

            $bMultiShop = oxRegistry::getConfig()->getShopId() !== 'oxbaseshop';
            if ($bMultiShop) {
                if (is_dir($sConfigPath . '/' . oxRegistry::getConfig()->getShopId())) {
                    $sConfigPath .= '/' . oxRegistry::getConfig()->getShopId();
                }
            }

            $sYAMMConfigFile = $sConfigPath . '/yamm.config.php';

            $this->_sConfigPath = $sYAMMConfigFile;
        }

        return $this->_sConfigPath;
    }

    /**
     * Determines wether a config could be found or not
     *
     * @return mixed
     */
    public function configFound()
    {
        $sYAMMConfigFile = $this->getConfigPath();
        if (file_exists($sYAMMConfigFile)) {
            $this->loadConfig($sYAMMConfigFile);
            return true;
        }

        return false;
    }

    private function loadConfig($configFile) {
        include ($configFile);
        $this->_staticEntries = $aYAMMConfig;
    }

    /**
     * Returns the enabled modules from this loaded config
     *
     * @return array
     */
    public function getEnabledModules()
    {
        return isset($this->_staticEntries['aYAMMEnabledModules']) ?  $this->_staticEntries['aYAMMEnabledModules'] : array();
    }

    /**
     * Returns the disabled modules from this loaded config
     *
     * @return array
     */
    public function getDisabledModules()
    {
        return isset($this->_staticEntries['aYAMMDisabledModules']) ?  $this->_staticEntries['aYAMMDisabledModules'] : array();
    }

    /**
     * Returns the special class order from this loaded config
     *
     * @return array
     */
    public function getSpecialClassOrder()
    {
        return isset($this->_staticEntries['aYAMMSpecialClassOrder']) ?  $this->_staticEntries['aYAMMSpecialClassOrder'] : array();
    }

    /**
     * Returns the module paths from this loaded config
     *
     * @return array
     */
    public function getModulePaths()
    {
        return isset($this->_staticEntries['aModulePaths']) ?  $this->_staticEntries['aModulePaths'] : array();
    }

    /**
     * Returns the block control from this loaded config
     *
     * @return array
     */
    public function getBlockControl()
    {
        return isset($this->_staticEntries['bYAMMBlockControl']) ?  $this->_staticEntries['bYAMMBlockControl'] : false;
    }

    /**
     * @param string|null $sConfigType The type of config to check for
     *
     * @return mixed
     */
    public function getConfigModificationTime($sConfigType = null)
    {
        $sYAMMConfig = $this->getConfigPath();
        return file_exists($sYAMMConfig) ? filemtime($this->getConfigPath()) : null;
    }
}