<?php

/**
 * Class yamm_config_loader_csv
 *
 * Loads the YAMM Config from CSV Files
 */
class yamm_config_loader_csv implements yamm_config_loader_interface {


    private $aConfig = array();

    public function __construct()
    {
        $this->aConfig = array(
            yamm_config_loader_interface::CONFIG_TYPE_ENBALED_MODULES => array(),
            yamm_config_loader_interface::CONFIG_TYPE_DISABLED_MODULES => array(),
            yamm_config_loader_interface::CONFIG_TYPE_SPECIAL_CLASS_ORDER => array(),
            yamm_config_loader_interface::CONFIG_TYPE_MODULE_PATHS => array(),
            yamm_config_loader_interface::CONFIG_TYPE_BLOCK_CONTROLL => false
        );
    }

    /**
     * @param $sConfigType
     *
     * @throws
     */
    private function getConfigFileByConfigType($sConfigType) {
        if (isset($this->aConfig[$sConfigType])) {
            return $sConfigType . '.csv';
        }

        throw new \yamm_invalid_argument_exception(sprintf("Config Type '%s' not supported", $sConfigType));
    }

    /**
     * Returns the path of the config file to use
     * @return null|string
     */
    private function getConfigPath($sConfigType) {
        $sConfigFile = $this->getConfigFileByConfigType($sConfigType);

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

        $sYAMMConfigFile = $sConfigPath . '/' . $sConfigFile;

        return file_exists($sYAMMConfigFile) ? $sYAMMConfigFile : false;
    }

    /**
     * Determines wether a config could be found or not
     *
     * @return mixed
     */
    public function configFound()
    {
        $return = false;

        // We need at least a config for enabled modules
        $sYAMMConfigFile = $this->getConfigPath(yamm_config_loader_interface::CONFIG_TYPE_ENBALED_MODULES);
        if (file_exists($sYAMMConfigFile)) {
            $this->loadModuleConfig($sYAMMConfigFile, yamm_config_loader_interface::CONFIG_TYPE_ENBALED_MODULES);
            $return  = true;
        }

        $sYAMMConfigFile = $this->getConfigPath(yamm_config_loader_interface::CONFIG_TYPE_DISABLED_MODULES);
        if (file_exists($sYAMMConfigFile)) {
            $this->loadModuleConfig($sYAMMConfigFile, yamm_config_loader_interface::CONFIG_TYPE_DISABLED_MODULES);
            $return  = true;
        }

        $sYAMMConfigFile = $this->getConfigPath(yamm_config_loader_interface::CONFIG_TYPE_SPECIAL_CLASS_ORDER);
        if (file_exists($sYAMMConfigFile)) {
            $this->loadSpecialClassOrderConfig($sYAMMConfigFile);
            $return  = true;
        }

        return $return;
    }

    /**
     * Loads enabled and disabled modules from a CSV File, that contains a matrix of ShopIDs and Modules,
     * where each column represents a shop and each row a module.
     * @param $configPath
     */
    private function loadModuleConfig($configPath, $configType)
    {
        $fHandle = fopen($configPath, "r");
        $rowCount = 0;
        $shopIdColumn = -1;

        $bMultiShop = oxRegistry::getConfig()->getShopId() !== 'oxbaseshop';
        if ($bMultiShop) {
            $iShopId = oxRegistry::getConfig()->getShopId();
        }

        while ($row = fgetcsv($fHandle, 1024, ";")) {
            // The first row, the header of the CSV file, contains the shop ids, for a multishop setup
            if ($rowCount == 0) {
                $shopIdMap = array_flip($row);
                // If it is a multishop setup, store the column for the current shop id
                if (isset($iShopId)) {
                    $shopIdColumn = $shopIdMap[$iShopId];
                } else {
                    // Otherwise assume, it is only one column (for one shop)
                    $shopIdColumn = 1;
                }
            } else {
                $module = $row[0];
                $active = (bool) trim($row[$shopIdColumn]) != '';

                if ($active) {
                    $this->aConfig[$configType][] = $module;
                }
            }

            $rowCount++;
        }

    }

    private function loadSpecialClassOrderConfig($configPath)
    {
        $fHandle = fopen($configPath, "r");

        $rowCount = 0;
        $specialClassOrder = array();
        while ($row = fgetcsv($fHandle, 1024, ";")) {
            // The first row, the header of the CSV file, contains the shop ids, for a multishop setup
            if ($rowCount == 0) {
                $header = $row;
                foreach ($header as $extendedClass) {
                    $specialClassOrder[$extendedClass] = array();
                }
            } else {
                foreach ($row as $index => $module) {
                    $module = trim($module);
                    if ($module != '') {
                        $specialClassOrder[$header[$index]][] = $module;
                    }
                }

            }

            $rowCount++;
        }

        $this->aConfig[yamm_config_loader_interface::CONFIG_TYPE_SPECIAL_CLASS_ORDER] = $specialClassOrder;
    }

    /**
     * @param string|null $sConfigType The type of config to check for
     *
     * @return mixed
     */
    public function getConfigModificationTime($sConfigType = null)
    {
        $fileModificationTime = 0;
        if (is_null($sConfigType)) {
            foreach ($this->aConfig as $sConfigType => $aConfig) {
                $configFile = $this->getConfigPath($sConfigType);
                if (!$configFile) {
                    continue;
                }

                $fileTime = filemtime($configFile);
                if ($fileTime > $fileModificationTime) {
                    $fileModificationTime = $fileTime;
                }
            }

        } else {
            $configFile = $this->getConfigPath($sConfigType);
            $fileModificationTime = filemtime($configFile);
        }

        return $fileModificationTime;
    }

    /**
     * Returns the enabled modules from this loaded config
     *
     * @return array
     */
    public function getEnabledModules()
    {
        return $this->aConfig[yamm_config_loader_interface::CONFIG_TYPE_ENBALED_MODULES];
    }

    /**
     * Returns the disabled modules from this loaded config
     *
     * @return array
     */
    public function getDisabledModules()
    {
        return $this->aConfig[yamm_config_loader_interface::CONFIG_TYPE_DISABLED_MODULES];
    }

    /**
     * Returns the special class order from this loaded config
     *
     * @return array
     */
    public function getSpecialClassOrder()
    {
        return $this->aConfig[yamm_config_loader_interface::CONFIG_TYPE_SPECIAL_CLASS_ORDER];
    }

    /**
     * Returns the module paths from this loaded config
     *
     * @return array
     */
    public function getModulePaths()
    {
        return $this->aConfig[yamm_config_loader_interface::CONFIG_TYPE_MODULE_PATHS];
    }

    /**
     * Returns the block control from this loaded config
     *
     * @return array
     */
    public function getBlockControl()
    {
        return $this->aConfig[yamm_config_loader_interface::CONFIG_TYPE_BLOCK_CONTROLL];
    }
}