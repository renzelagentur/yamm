<?php

class MyRecursiveFilterIterator extends RecursiveFilterIterator
{

    public static $FILTERS = array(
        'metadata.php',
    );

    public function accept()
    {
        return !in_array(
            $this->current()->getFilename(),
            self::$FILTERS,
            true
        );
    }
}

class yamm_module_cleanup
{

    private $moduleDir = null;
    
    private $errorCallback;

    /**
     * @var array
     */
    protected $moduleMetaData = array();

    protected $oConf = null;

    protected $oDbConnection = null;

    protected $_aModules = array();

    public function __construct(\oxLegacyDb $db)
    {
        $this->moduleDir = realpath(dirname(__FILE__)) . '/../../../';
        $this->loadModuleMetadata();

        $this->oDbConnection = $db;
    }

    protected function loadModuleMetadata()
    {

        $dirItr = new RecursiveDirectoryIterator($this->moduleDir);
        $filterItr = new MyRecursiveFilterIterator($dirItr);
        $itr = new RecursiveIteratorIterator($filterItr, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($itr as $filePath => $fileInfo) {

            $aModule = array();
            if (file_exists($fileInfo->getRealPath() . '/metadata.php')) {
                /* @var splfileinfo $fileInfo */
                $aModule = $this->parseMetaDataFile($fileInfo->getRealPath() . '/metadata.php');

                $this->moduleMetaData[$aModule['id']] = $aModule;
            }
        }


    }

    public function cleanUpModuleExtends()
    {
        $sQuery = sprintf('SELECT OXID, OXSHOPID, OXVARNAME, OXVARTYPE, DECODE(oxvarvalue, "%s") as OXVARVALUE FROM oxconfig WHERE OXVARNAME = "aModules"', oxRegistry::getConfig()->getConfigParam('sConfigKey'));

        $result = $this->oDbConnection->getAll($sQuery);
        foreach ($result as $conf) {
            $varValue = unserialize($conf['OXVARVALUE']);
            $bChanged = false;
            if ($varValue) {
                foreach ($varValue as $baseClass => $extendString) {
                    $extends = explode('&', $extendString);

                    foreach ($extends as $index => $extend) {
                        if (!file_exists($this->moduleDir . $extend . '.php')) {
                            unset($extends[$index]);
                            $bChanged = true;
                        }
                    }

                    $extendString = implode('&', $extends);

                    if ($extendString == '') {
                        unset($varValue[$baseClass]);
                    } else {
                        $varValue[$baseClass] = $extendString;
                    }
                }

                if ($bChanged) {
                    $conf['OXVARVALUE'] = serialize($varValue);

                    $sUpdateSsql = sprintf('UPDATE oxconfig SET OXVARVALUE = ENCODE("%s", "%s") WHERE OXID = "%s"', $this->oDbConnection->qStr($conf['OXVARVALUE']), oxRegistry::getConfig()->getConfigParam('sConfigKey'), $conf['OXID']);
                    if (!$this->oDbConnection->execute($sUpdateSsql)) {
                        throw new \RuntimeException($this->oDbConnection->error);
                    }

                }
            }
        }
    }

    public function cleanUpModuleFiles()
    {
        $sQuery = sprintf('SELECT OXID, OXSHOPID, OXVARNAME, OXVARTYPE, DECODE(oxvarvalue, "%s") as OXVARVALUE FROM oxconfig WHERE OXVARNAME = "aModuleFiles"', oxRegistry::getConfig()->getConfigParam('sConfigKey'));

        $result = $this->oDbConnection->getAll($sQuery);
        foreach ($result as $conf) {
            $varValue = unserialize($conf['OXVARVALUE']);

            $bChanged = false;

            if ($varValue) {
                foreach ($varValue as $module => $files) {

                    if (is_array($files)) {
                        foreach ($files as $index => $file) {
                            if (!file_exists(realpath(dirname(__FILE__)) . '/../../modules/' . $file)) {
                                unset($files[$index]);
                                $bChanged = true;
                            }
                        }
                        if (count($files) == 0) {
                            unset($varValue[$module]);
                        } else {
                            $varValue[$module] = $files;
                        }
                    } else {
                        unset($varValue[$module]);
                    }

                }

                if ($bChanged) {
                    $conf['OXVARVALUE'] = serialize($varValue);

                    $sUpdateSsql = sprintf('UPDATE oxconfig SET OXVARVALUE = ENCODE("%s", "%s") WHERE OXID = "%s"', $this->oDbConnection->qStr($conf['OXVARVALUE']), oxRegistry::getConfig()->getConfigParam('sConfigKey'), $conf['OXID']);
                    if (!$this->oDbConnection->execute($sUpdateSsql)) {
                        throw new \RuntimeException($this->oDbConnection->error);
                    }
                }
            }
        }
    }

    public function cleanUpDisabledModules()
    {
        $sQuery = sprintf('SELECT OXID, OXSHOPID, OXVARNAME, OXVARTYPE, DECODE(oxvarvalue, "%s") as OXVARVALUE FROM oxconfig WHERE OXVARNAME = "aDisabledModules"', oxRegistry::getConfig()->getConfigParam('sConfigKey'));

        $result = $this->oDbConnection->getAll($sQuery);
        foreach ($result as $conf) {
            $bChanged = false;
            $varValue = unserialize($conf['OXVARVALUE']);
            if ($varValue) {
                foreach ($varValue as $i => $moduleId) {
                    if (!isset($this->moduleMetaData[$moduleId])) {
                        unset($varValue[$i]);
                        $bChanged = true;
                    }
                }

                if ($bChanged) {
                    $conf['OXVARVALUE'] = serialize($varValue);

                    $sUpdateSsql = sprintf('UPDATE oxconfig SET OXVARVALUE = ENCODE("%s", "%s") WHERE OXID = "%s"', $this->oDbConnection->qStr($conf['OXVARVALUE']), oxRegistry::getConfig()->getConfigParam('sConfigKey'), $conf['OXID']);
                    if (!$this->oDbConnection->execute($sUpdateSsql)) {
                        throw new \RuntimeException($this->oDbConnection->error);
                    }
                }
            }
        }
    }

    public function cleanupDuplicateBlocks()
    {
        $sQuery = "SELECT COUNT(*), oxshopid, oxmodule, oxfile, oxblockname FROM oxtplblocks GROUP BY oxshopid, oxmodule, oxfile, oxblockname HAVING COUNT(*) > 1";
        $result = $this->oDbConnection->getAll($sQuery);
        foreach ($result as $conf) {
            $sSQL = sprintf("SELECT OXID FROM oxtplblocks WHERE oxshopid = %d AND OXMODULE = '%s' AND OXFILE = '%s' AND OXBLOCKNAME = '%s' ORDER BY OXTIMESTAMP DESC", $blockInfo['oxshopid'], $blockInfo['oxmodule'], $blockInfo['oxfile'], $blockInfo['oxblockname']);
            $latestBlock = $this->oDbConnection->getOne($sSQL);

            $delSql = sprintf("DELETE FROM oxtplblocks WHERE oxshopid = %d AND OXMODULE = '%s' AND OXFILE = '%s' AND OXBLOCKNAME = '%s' AND OXID <> '%s'", $blockInfo['oxshopid'], $blockInfo['oxmodule'], $blockInfo['oxfile'], $blockInfo['oxblockname'], $latestBlock['OXID']);
            $this->oDbConnection->execute($delSql);

        }
    }

    public function cleanUpModulePaths()
    {
        $this->getModulesFromDir($this->moduleDir);
        $sQuery = sprintf('SELECT OXID, OXSHOPID, OXVARNAME, OXVARTYPE, DECODE(oxvarvalue, "%s") as OXVARVALUE FROM oxconfig WHERE OXVARNAME = "aModulePaths"', oxRegistry::getConfig()->getConfigParam('sConfigKey'));

        $result = $this->oDbConnection->getAll($sQuery);
        foreach ($result as $conf) {

            $currentPaths = unserialize($conf['OXVARVALUE']);
            if ($currentPaths) {
                foreach ($currentPaths as $moduleIde => $modulePath) {
                    if (!isset($this->_aModules[$moduleIde])) {
                        unset($currentPaths[$moduleIde]);
                    }
                }

                $sUpdateSql = sprintf('UPDATE oxconfig SET OXVARVALUE = ENCODE("%s", "%s") WHERE OXVARNAME = "aModulePaths" AND OXSHOPID = %d', $this->oDbConnection->qStr(serialize($currentPaths)), oxRegistry::getConfig()->getConfigParam('sConfigKey'), $conf['OXSHOPID']);

                if (!$this->oDbConnection->execute($sUpdateSql)) {
                    throw new \RuntimeException($this->oDbConnection->error);
                }
            }
        }
    }

    /**
     * Scans modules dir and returns collected modules list.
     * Recursively loads also modules that are in vendor directory.
     *
     * @param string $sModulesDir Main module dir path
     * @param string $sVendorDir  Vendor directory name
     *
     * @return array
     */
    public function getModulesFromDir($sModulesDir, $sVendorDir = null)
    {
        foreach (glob($sModulesDir . '*') as $sModuleDirPath) {

            $sModuleDirPath .= (is_dir($sModuleDirPath)) ? '/' : '';
            $sModuleDirName = basename($sModuleDirPath);

            // skipping some file
            if ((!is_dir($sModuleDirPath) && substr($sModuleDirName, -4) != ".php")) {
                continue;
            }

            if ($this->_isVendorDir($sModuleDirPath)) {
                // scanning modules vendor directory
                $this->getModulesFromDir($sModuleDirPath, basename($sModuleDirPath));
            } else {
                // loading module info
                $sModuleDirName = (!empty($sVendorDir)) ? $sVendorDir . '/' . $sModuleDirName : $sModuleDirName;

                if (file_exists($this->moduleDir . $sModuleDirName . '/metadata.php')) {
                    $aModule = $this->parseMetaDataFile($this->moduleDir . $sModuleDirName . '/metadata.php');

                    $this->_aModules[$aModule['id']] = $sModuleDirName;
                } else {
                    $this->_aModules[$sModuleDirName] = $sModuleDirName;
                }
            }
        }
    }


    /**
     * Checks if directory is vendor directory.
     *
     * @param string $sModuleDir dir path
     *
     * @return bool
     */
    protected function _isVendorDir($sModuleDir)
    {
        if (is_dir($sModuleDir) && file_exists($sModuleDir . 'vendormetadata.php')) {
            return true;
        }

        return false;
    }

    protected function parseMetaDataFile($metaDataFile)
    {
        if (file_exists($metaDataFile) && is_readable($metaDataFile)) {
            include $metaDataFile;
            return $aModule;
        }
        return null;
    }
} 