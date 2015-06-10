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

use RA\OxidCleanupScripts\OxidCleanup;

require_once __DIR__ . '/../services/yamm_config_loader_factory.php';

class yamm_oxutilsobject extends yamm_oxutilsobject_parent
{

    protected $_staticEntries = null;
    private $moduleCleanup;

    /**
     * @var yamm_config_loader_interface
     */
    private $configLoader;

    /**
     * YAMM Variable Name for Enabled Modules
     */
    const ENABLED = 'aYAMMEnabledModules';

    /**
     * YAMM Variable Name for disabled Modules
     */
    const DISABLED = 'aYAMMDisabledModules';

    /**
     * YAMM Variable Name for Special Class Ordering
     */
    const CLASS_ORDER = 'aYAMMSpecialClassOrder';

    /**
     * YAMM Variable for Block Control
     */
    const BLOCK_CONTROL = 'bYAMMBlockControl';

    /**
     * Variable Name for the Cached config
     */
    const CACHED_CONFIG = 'aYAMMCachedConfig';

    /**
     * Config Variable Name, for the timestamp on which the config has been last modified
     */
    const LAST_MODIFIED = 'iYAMMLastModified';

    /**
     * YAMM Variable Name for Module Paths
     */
    const MODULE_PATHS = 'aModulePaths';

    /**
     * Set to true, if initYAMM has been called allready, to make sure it is only executed once
     * @var bool
     */
    static $_bInitCalled = false;

    /**
     * Calsl a method to activate or deactivate a module
     *
     * @param        $oModule
     * @param string $method
     */
    private function activate($oModule, $method = 'activate')
    {
        if ( class_exists('oxModuleInstaller') ) {
            oxRegistry::get('oxModuleInstaller')->$method($oModule);
        } else {
            $oModule->$method();
        }
    }

    /**
     * Creates an instance of the Module Cleanup service and returns it
     */
    private function getModuleCleanupService()
    {
        if (is_null($this->moduleCleanup)) {
            $this->moduleCleanup = new OxidCleanup(getShopBasePath());
            $this->moduleCleanup->registerOutputHandler(function($message) {
                    $this->log($message);
                });
        }

        return $this->moduleCleanup;
    }

    /**
     * Checks for changes in the YAMM Config or Module Metadata and reacty to it accordingly
     * @param $modulePaths
     */
    private function handleConfigChanges($modulePaths)
    {
        $data = oxRegistry::getConfig()->getShopConfVar(self::CACHED_CONFIG, null, 'yamm/yamm');
        $oModule = oxNew('oxModule');
        if ( !$data ) {
            $data = array('metafiles' => array(), 'config' => array(self::ENABLED => array(), self::DISABLED => array(), ));
        }
        $newlyActivated = array();

        $bCleanupRun = false;


        $configModTime = $this->configLoader->getConfigModificationTime();
        if ( oxRegistry::getConfig()->getShopConfVar(self::LAST_MODIFIED, null, 'yamm/yamm') < $configModTime || defined('YAMM_FORCE_RELOAD') ) {

            $this->getModuleCleanupService()->fullCleanUp();

            $bCleanupRun = true;

            foreach ($this->_staticEntries[self::ENABLED] as $id) {
                $oModule->load($id);
                if ( !$oModule->isActive() ) {
                    error_log("Activate {$id}");
                    $this->activate($oModule);
                    $newlyActivated[] = $id;
                }
            }

            // @formatter:off
            $toDeactivate = array_diff(
                $this->_staticEntries[self::DISABLED],
                $this->_staticEntries[self::ENABLED]
            );
            // @formatter:on
            foreach ($toDeactivate as $id) {
                if ( $oModule->load($id) ) {
                    if ( $oModule->isActive() ) {
                        error_log("Deactivate {$id}");
                        $this->activate($oModule, 'deactivate');
                    }
                }
            }
        }

        $aModulesToReactivate = array();

        // Reactivate modules whoms metadata.php has changed.
        // Must be done to ensure that blocks are loaded, otherwise
        // some modules might break.
        foreach ($this->_staticEntries[self::ENABLED] as $id) {
            if ( in_array($id, $newlyActivated) || $id == 'yamm/yamm' )
                continue;

            $metaFile = rtrim(getShopBasePath(), '/') . '/modules/' . $modulePaths[$id] . '/metadata.php';

            if (file_exists($metaFile) && filemtime($metaFile) > $data['metafiles'][$id]['last_modified'] ) {
                $this->log(sprintf("Metadata of module '%s' has changed. Reactivating it.", $id));
                $aModulesToReactivate[] = $id;
            }
        }

        if (count($aModulesToReactivate) > 0)
        {
            if (!$bCleanupRun) {
                $this->getModuleCleanupService()->fullCleanUp();
            }

            foreach ($aModulesToReactivate as $moduleId) {
                $this->reactivateModule($moduleId);
            }
        }

        $data = array('config' => $this->_staticEntries, 'metafiles' => array(), );
        foreach ($this->_staticEntries[self::ENABLED] as $id) {
            $metaFile = rtrim(getShopBasePath(), '/') . '/modules/' . $modulePaths[$id] . '/metadata.php';
            if (file_exists($metaFile)) {
                $data['metafiles'][$id] = array('metafile' => $metaFile, 'last_modified' => filemtime($metaFile), );
            }
        }
        oxRegistry::getConfig()->saveShopConfVar('arr', self::CACHED_CONFIG, $data, null, 'yamm/yamm');
        oxRegistry::getConfig()->saveShopConfVar('num', self::LAST_MODIFIED, $configModTime, null, 'yamm/yamm');
    }

    /**
     * @param $class
     *
     * @return array
     */
    private function getOrderForClass($class)
    {
        $result = $this->_staticEntries[self::ENABLED];
        if ( isset($this->_staticEntries[self::CLASS_ORDER]) ) {
            if ( array_key_exists($class, $this->_staticEntries[self::CLASS_ORDER]) ) {
                $result = array_merge(array_diff($result, $this->_staticEntries[self::CLASS_ORDER][$class]), $this->_staticEntries[self::CLASS_ORDER][$class]);
            }
        }

        return $result;
    }

    private function extendsForClass($class)
    {
        return array_key_exists($class, $this->_staticEntries['aModules']) ? $this->_staticEntries['aModules'][$class] : array();
    }

    /**
     * Initializes all YAMM Config Variables
     */
    private function initYAMM()
    {

        $startTime = microtime(true);
        // Only execute the heavy lifting once
        if (self::$_bInitCalled) {
            return;
        } else {
            self::$_bInitCalled = true;
        }

        if (defined('YAMM_CONFIG_TYPE')) {
            $configType = YAMM_CONFIG_TYPE;
            $this->configLoader = yamm_config_loader_factory::getLoader($configType);
        } else {
            $this->configLoader = yamm_config_loader_factory::getLoader();
        }

        /**
         * @var $configLoader yamm_config_loader_interface
         */
        if ($this->configLoader->configFound() && (!isset($this->_staticEntries) || defined('YAMM_FORCE_RELOAD')) ) {

            $this->_staticEntries = array(
                self::ENABLED       => $this->configLoader->getEnabledModules(),
                self::DISABLED      => $this->configLoader->getDisabledModules(),
                self::CLASS_ORDER   => $this->configLoader->getSpecialClassOrder(),
                self::BLOCK_CONTROL => $this->configLoader->getBlockControl(),
                self::MODULE_PATHS  => $this->configLoader->getModulePaths()
            );

            $configLoadTime = microtime(true) - $startTime;
            $this->log(sprintf("Config Load Time %f seconds", $configLoadTime));

            $modulePaths = array_merge(parent::getModuleVar('aModulePaths'), isset($this->_staticEntries['aModulePaths']) ? $this->_staticEntries['aModulePaths'] : array());

            $this->handleConfigChanges($modulePaths);

            $this->_staticEntries['aModules']           = parent::getModuleVar('aModules');
            $this->_staticEntries['aModuleFiles']       = parent::getModuleVar('aModuleFiles') ? parent::getModuleVar('aModuleFiles') : array();
            $this->_staticEntries['aModuleTemplates']   = parent::getModuleVar('aModuleTemplates') ? parent::getModuleVar('aModuleTemplates') : array();

            foreach ($this->_staticEntries['aModules'] as $key => $value) {
                $this->_staticEntries['aModules'][$key] = explode('&', $value);
            }

            $moduleMeta = array();
            foreach ($this->_staticEntries[self::ENABLED] as $module) {
                $metaFile = getShopBasePath() . '/modules/' . $modulePaths[$module] . '/metadata.php';
                $aModule = array();
                @include ($metaFile);
                $moduleMeta[$module] = $aModule;
                $this->_staticEntries['aModuleTemplates'][$module] = isset($aModule['templates']) ? $aModule['templates'] : null;
                $this->_staticEntries['aModuleFiles'][$module] = isset($aModule['files']) ? array_change_key_case($aModule['files'], CASE_LOWER) : null;
            }

            // @formatter:off
            $extensions = array_map(function($meta) {
                    return array_key_exists('extend', $meta) ? array_keys($meta['extend']) : array();
                }, $moduleMeta);
            // @formatter:on
            $extensions = call_user_func_array(array_merge, array_values($extensions));
            $extensions = array_unique($extensions);

            foreach ($extensions as $class) {
                $classes = array();
                foreach ($this->getOrderForClass($class) as $module) {
                    if ( isset($moduleMeta[$module]['extend']) && array_key_exists($class, $moduleMeta[$module]['extend']) ) {
                        $classes[] = $moduleMeta[$module]['extend'][$class];
                    }
                }

                $this->_staticEntries['aModules'][$class] = array_merge(array_diff($this->extendsForClass($class), $classes), $classes);
            }

            foreach ($this->_staticEntries['aModules'] as $key => $value) {
                $this->_staticEntries['aModules'][$key] = implode('&', $value);
            }
        }

        $runTime = microtime(true) - $startTime;
        $this->log(sprintf("InitYAMM runtime %f seconds", $runTime));
    }

    /**
     * Writes a message to the yamm.log
     * @param $message
     */
    private function log($message) {
        oxRegistry::getUtils()->writeToLog(sprintf("%s: %s \n", date("Y-m-d H:i:s"), $message), 'yamm.log');
    }

    /**
     * Reactivates a module
     *
     * @param $oModule
     * @param $id
     */
    private function reactivateModule($id)
    {
        $oModule = oxNew('oxModule');
        $oModule->load($id);

        // Since 4.9/EE5.2 Module Configs are deleted, when you deactivate a module,
        // this is why we temporarly backup the config to restore it again after activation
        $sQuery = sprintf(
            'SELECT OXID, OXSHOPID, OXMODULE, OXVARNAME, OXVARTYPE, DECODE(oxvarvalue, "%s") as OXVARVALUE FROM oxconfig WHERE OXSHOPID = "%s" AND OXMODULE = "module:%s"',
            oxRegistry::getConfig()->getConfigParam('sConfigKey'),
            oxRegistry::getConfig()->getShopId(),
            $id
        );
        $aResult = oxDb::getDb()->getArray($sQuery);

        $this->activate($oModule, 'deactivate');
        $this->activate($oModule);

        foreach ($aResult as $aRow) {
            if ($aRow[3] != 'noConfigHere') {
                $sQuery = sprintf(
                    'INSERT INTO oxconfig SET OXID= %s, OXSHOPID = %s, OXMODULE = %s,OXVARNAME = %s, OXVARTYPE = %s, OXVARVALUE =ENCODE(%s, %s);',
                    oxDb::getDb()->qstr($aRow[0]),
                    oxDb::getDb()->qstr($aRow[1]),
                    oxDb::getDb()->qstr($aRow[2]),
                    oxDb::getDb()->qstr($aRow[3]),
                    oxDb::getDb()->qstr($aRow[4]),
                    oxDb::getDb()->qstr($aRow[5]),
                    oxDb::getDb()->qstr(oxRegistry::getConfig()->getConfigParam('sConfigKey'))
                );

                oxDb::getDb()->query($sQuery);
            }
        }
    }

    public function getYAMMKeys()
    {
        $this->initYAMM();
        return isset($this->_staticEntries) ? array_keys($this->_staticEntries) : array();
    }

    public function hasYAMMKey($key)
    {
        $this->initYAMM();
        return isset($this->_staticEntries) && array_key_exists($key, $this->_staticEntries);
    }

    public function getModuleVar($sModuleVarName)
    {
        $this->initYAMM();

        if ( isset($this->_staticEntries) && array_key_exists($sModuleVarName, $this->_staticEntries) ) {
            if ( $sModuleVarName === 'aDisabledModules' ) {
                // @formatter:off

                // Merge YAMM config with deactivated modules from Oxid DB, then make sure that YAMM enabled modules
                // are active, even if otherwise deactivated
                $result = array_diff(
                    array_merge(
                        parent::getModuleVar($sModuleVarName),
                        $this->_staticEntries[self::DISABLED]
                    ),
                    $this->_staticEntries[self::ENABLED]
                );
                // @formatter:on
            } elseif ( is_array($this->_staticEntries[$sModuleVarName]) && parent::getModuleVar($sModuleVarName) ) {
                $old = parent::getModuleVar($sModuleVarName);
                $new = $this->_staticEntries[$sModuleVarName];
                $result = ($new == $old) ? $new : array_merge($old, $new);
            } else {
                $result = $this->_staticEntries[$sModuleVarName];
            }
        } else {
            $result = parent::getModuleVar($sModuleVarName);
        }


        return $result;
    }

}

