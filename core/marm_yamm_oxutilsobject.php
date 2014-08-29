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


class marm_yamm_oxutilsobject extends marm_yamm_oxutilsobject_parent
{

    protected $_sConfigFile = 'marm_yamm.config.php';

    protected $_staticEntries = null;
    
    const ENABLED = 'aYAMMEnabledModules';
    const DISABLED = 'aYAMMDisabledModules';
    const CLASS_ORDER = 'aYAMMSpecialClassOrder';
    const BLOCK_CONTROL = 'bYAMMBlockControl';

    private function handleConfigChanges()
    {
        $cache = getShopBasePath() . 'tmp/' . $this->_sConfigFile;
        if ( file_exists($cache) && filemtime(getShopBasePath() . $this->_sConfigFile) > filemtime($cache) )
        {
            include ($cache);
            $oModule = oxNew('oxModule');
            $toActivate = array_diff($this->_staticEntries[self::ENABLED], $aYAMMConfig[self::ENABLED]);
            foreach ( $toActivate as $id )
            {
                $oModule->load($id);
                $oModule->activate();
            }
            // @formatter:off
            $toDeactivate = array_diff(
                $this->_staticEntries[self::DISABLED],
                $aYAMMConfig[self::DISABLED],
                $this->_staticEntries[self::ENABLED]
            );
            // @formatter:on
            foreach ( $toDeactivate as $id )
            {
                $oModule->load($id);
                $oModule->deactivate();
            }
        }
        copy(getShopBasePath() . $this->_sConfigFile, $cache);
    }

    public function getYAMMKeys()
    {
        return $this->_staticEntries ? array_keys($this->_staticEntries) : array();
    }
    
    private function getOrderForClass($class)
    {
        $result = $this->_staticEntries[self::ENABLED];
        if ( isset($this->_staticEntries[self::CLASS_ORDER]) )
        {
            if ( array_key_exists($class, $this->_staticEntries[self::CLASS_ORDER]) )
            {
                $result = array_merge(array_diff($result, $this->_staticEntries[self::CLASS_ORDER][$class]), $this->_staticEntries[self::CLASS_ORDER][$class]);
            }
        }
        return $result;
    }
    
    private function extendsForClass($class)
    {
        return array_key_exists($class, $this->_staticEntries['aModules']) ? $this->_staticEntries['aModules'][$class] : array();
    }

    public function getModuleVar($sModuleVarName)
    {

        if ( !isset($this->_staticEntries) && file_exists(getShopBasePath() . $this->_sConfigFile) )
        {
            include (getShopBasePath() . $this->_sConfigFile);
            $this->_staticEntries = $aYAMMConfig;
            $this->handleConfigChanges();
            $this->_staticEntries['aModules'] = parent::getModuleVar('aModules');
            foreach ( $this->_staticEntries['aModules'] as $key => $value )
            {
                $this->_staticEntries['aModules'][$key] = explode('&', $value);
            }
            
            $moduleMeta = array();
            $modulePathes = array_merge(parent::getModuleVar('aModulePaths'), isset($this->_staticEntries['aModulePaths']) ? $this->_staticEntries['aModulePaths'] : array());
            foreach ( $this->_staticEntries[self::ENABLED] as $module )
            {
                //$metaFile = getShopBasePath() . '/modules/' . $this->_staticEntries['aModulePaths'][$module] . '/metadata.php';
                $metaFile = getShopBasePath() . '/modules/' . $modulePathes[$module] . '/metadata.php';
                $aModule = array();
                @include($metaFile);
                $moduleMeta[$module] = $aModule;
            }
            
            $extensions = array_map(function($meta) { return array_key_exists('extend', $meta) ? array_keys($meta['extend']) : array(); }, $moduleMeta);
            $extensions = call_user_func_array(array_merge, array_values($extensions));
            $extensions = array_unique($extensions);
            
            foreach ($extensions as $class)
            {
                $classes = array();
                foreach ( $this->getOrderForClass($class) as $module )
                {
                    if ( isset($moduleMeta[$module]['extend']) && array_key_exists($class, $moduleMeta[$module]['extend']) )
                    {
                        $classes[] = $moduleMeta[$module]['extend'][$class];
                    }
                }

                $this->_staticEntries['aModules'][$class] = array_merge(array_diff($this->extendsForClass($class), $classes), $classes);
            }
            
            foreach ( $this->_staticEntries['aModules'] as $key => $value )
            {
                $this->_staticEntries['aModules'][$key] = implode('&', $value);
            }
        }

        if ( isset($this->_staticEntries) && array_key_exists($sModuleVarName, $this->_staticEntries) )
        {
            if ( $sModuleVarName === 'aDisabledModules' )
            {
                // @formatter:off
        		return array_diff(
        		  array_merge(
        		      parent::getModuleVar($sModuleVarName),
        		      $this->_staticEntries[self::DISABLED]
                  ),
                  $this->_staticEntries[self::ENABLED]
                );
                // @formatter:on
            }
            elseif ( is_array($this->_staticEntries[$sModuleVarName]) && parent::getModuleVar($sModuleVarName) )
            {
                $old = parent::getModuleVar($sModuleVarName);
                $new = $this->_staticEntries[$sModuleVarName];
                return ($new == $old) ? $new : array_merge($old, $new);
            }
            else
            {
                return $this->_staticEntries[$sModuleVarName];
            }
        }
        $result = parent::getModuleVar($sModuleVarName);

        return $result;
    }

}
