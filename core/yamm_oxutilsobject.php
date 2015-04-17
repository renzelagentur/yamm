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

class yamm_oxutilsobject extends yamm_oxutilsobject_parent
{

    protected $_sConfigFile = 'yamm.config.php';

    protected $_staticEntries = null;

    const ENABLED = 'aYAMMEnabledModules';

    const DISABLED = 'aYAMMDisabledModules';

    const CLASS_ORDER = 'aYAMMSpecialClassOrder';

    const BLOCK_CONTROL = 'bYAMMBlockControl';

    const CACHED_CONFIG = 'aYAMMCachedConfig';

    const LAST_MODIFIED = 'iYAMMLastModified';

    private $bMultiShop;

    private $sYAMMConfigFile;

    private $sYAMMContext;

    private function activate($oModule, $method = 'activate')
    {
        if ( class_exists('oxModuleInstaller') ) {
            oxRegistry::get('oxModuleInstaller')->$method($oModule);
        } else {
            $oModule->$method();
        }
    }

    private function handleConfigChanges($modulePaths)
    {
        $data = oxRegistry::getConfig()->getShopConfVar(self::CACHED_CONFIG, null, 'yamm/yamm');
        $oModule = oxNew('oxModule');
        if ( !$data ) {
            $data = array('metafiles' => array(), 'config' => array(self::ENABLED => array(), self::DISABLED => array(), ));
        }
        $newlyActivated = array();

        if ( oxRegistry::getConfig()->getShopConfVar(self::LAST_MODIFIED, null, 'yamm/yamm') < filemtime($this->sYAMMConfigFile) || defined('YAMM_FORCE_RELOAD') ) {

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

        // Reactivate modules whoms metadata.php has changed.
        // Must be done to ensure that blocks are loaded, otherwise
        // some modules might break.
        foreach ($this->_staticEntries[self::ENABLED] as $id) {
            if ( in_array($id, $newlyActivated) || $id == 'yamm/yamm' )
                continue;

            $metaFile = rtrim(getShopBasePath(), '/') . '/modules/' . $modulePaths[$id] . '/metadata.php';
            if ( filemtime($metaFile) > $data['metafiles'][$id]['last_modified'] ) {
                error_log("Reactivate {$id}");
                $oModule->load($id);
                $this->activate($oModule, 'deactivate');
                $this->activate($oModule);
            }
        }

        $data = array('config' => $this->_staticEntries, 'metafiles' => array(), );
        foreach ($this->_staticEntries[self::ENABLED] as $id) {
            $metaFile = rtrim(getShopBasePath(), '/') . '/modules/' . $modulePaths[$id] . '/metadata.php';
            $data['metafiles'][$id] = array('metafile' => $metaFile, 'last_modified' => filemtime($metaFile), );
        }
        oxRegistry::getConfig()->saveShopConfVar('arr', self::CACHED_CONFIG, $data, null, 'yamm/yamm');
        oxRegistry::getConfig()->saveShopConfVar('num', self::LAST_MODIFIED, filemtime($this->sYAMMConfigFile), null, 'yamm/yamm');
    }

    public function getYAMMKeys()
    {
        return isset($this->_staticEntries) ? array_keys($this->_staticEntries) : array();
    }

    public function hasYAMMKey($key)
    {
        return isset($this->_staticEntries) && array_key_exists($key, $this->_staticEntries);
    }

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

    public function initYAMM()
    {

        $sConfigPath = rtrim(getShopBasePath(), '/') . '/YAMM';

        if (oxRegistry::getConfig()->getShopConfVar('sYAMMContext') !== null) {
            $this->sYAMMContext = oxRegistry::getConfig()->getShopConfVar('sYAMMContext');
        } else {
            $this->sYAMMContext = 'production';
        }

        if (is_dir($sConfigPath . '/' . $this->sYAMMContext)) {
            $sConfigPath .= '/' . $this->sYAMMContext;
        }

        $this->bMultiShop = oxRegistry::getConfig()->getShopId() !== 'oxbaseshop';
        if ($this->bMultiShop) {
            if (is_dir($sConfigPath . '/' . oxRegistry::getConfig()->getShopId())) {
                $sConfigPath .= '/' . oxRegistry::getConfig()->getShopId();
            }
        }

        $this->sYAMMConfigFile = $sConfigPath . '/' . $this->_sConfigFile;

        if (file_exists($this->sYAMMConfigFile) && (!isset($this->_staticEntries) || defined('YAMM_FORCE_RELOAD')) ) {
            include ($this->sYAMMConfigFile);
            $this->_staticEntries = $aYAMMConfig;
            $modulePaths = array_merge(parent::getModuleVar('aModulePaths'), isset($this->_staticEntries['aModulePaths']) ? $this->_staticEntries['aModulePaths'] : array());
            $this->handleConfigChanges($modulePaths);
            $this->_staticEntries['aModules'] = parent::getModuleVar('aModules');
            $this->_staticEntries['aModuleFiles'] = parent::getModuleVar('aModuleFiles') ? parent::getModuleVar('aModuleFiles') : array();
            $this->_staticEntries['aModuleTemplates'] = parent::getModuleVar('aModuleTemplates') ? parent::getModuleVar('aModuleTemplates') : array();

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
    }

    public function getModuleVar($sModuleVarName)
    {

        if ( isset($this->_staticEntries) && array_key_exists($sModuleVarName, $this->_staticEntries) ) {
            if ( $sModuleVarName === 'aDisabledModules' ) {
                // @formatter:off
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
