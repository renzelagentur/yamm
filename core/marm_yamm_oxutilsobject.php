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

    private function handleConfigChanges()
    {
        $cache = getShopBasePath() . 'tmp/' . $this->_sConfigFile;
        if ( file_exists($cache) && filemtime(getShopBasePath() . $this->_sConfigFile) > filemtime($cache) )
        {
            include ($cache);
            $oModule = oxNew('oxModule');
            $toActivate = array_diff($this->_staticEntries['aYAMMEnabledModules'], $aYAMMConfig['aYAMMEnabledModules']);
            foreach ( $toActivate as $id )
            {
                $oModule->load($id);
                $oModule->activate();
            }
            // @formatter:off
            $toDeactivate = array_diff(
                $this->_staticEntries['aYAMMDisabledModules'],
                $aYAMMConfig['aYAMMDisabledModules'],
                $this->_staticEntries['aYAMMEnabledModules']
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
        return array_keys($this->_staticEntries);
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
            foreach ( $this->_staticEntries['aYAMMEnabledModules'] as $module )
            {
                $metaPath = getShopBasePath() . 'modules/' . $this->_staticEntries['aModulePaths'][$module] . '/metadata.php';
                include ($metaPath);
                foreach ( $aModule['extend'] as $class => $path )
                {
                    if ( isset($this->_staticEntries['aModules'][$class]) )
                    {
                        if ( in_array($path, $this->_staticEntries['aModules'][$class]) )
                        {
                            if ( !$this->_staticEntries['bYAMMRenice'] )
                            {
                                continue;
                            }
                            if ( ($key = array_search($path, $this->_staticEntries['aModules'][$class])) !== false )
                            {
                                unset($this->_staticEntries['aModules'][$class][$key]);
                            }
                        }
                        $this->_staticEntries['aModules'][$class][] = $path;
                    }
                    else
                    {
                        $this->_staticEntries['aModules'][$class] = array($path);
                    }
                }
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
        		      $this->_staticEntries['aYAMMDisabledModules']
                  ),
                  $this->_staticEntries['aYAMMEnabledModules']
                );
                // @formatter:on
            }
            elseif ( is_array($this->_staticEntries[$sModuleVarName]) && parent::getModuleVar($sModuleVarName) )
            {
                return array_merge(parent::getModuleVar($sModuleVarName), $this->_staticEntries[$sModuleVarName]);
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
