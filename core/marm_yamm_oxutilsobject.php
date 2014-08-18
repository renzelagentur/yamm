<?php

if(false)
{
    class marm_yamm_oxutilsobject_parent extends oxUtilsObject{}
}

class marm_yamm_oxutilsobject extends marm_yamm_oxutilsobject_parent
{
    
    protected $_sConfigFile = 'marm_yamm.config.php';

    protected $_staticEntries = null;

    public function getModuleVar( $sModuleVarName )
    {
        
        if( !isset( $this->_staticEntries ) && file_exists( getShopBasePath() . $this->_sConfigFile ) )
        {
        	$this->_log[] = getShopBasePath() . $this->_sConfigFile;
            include( getShopBasePath() . $this->_sConfigFile );
			$this->_staticEntries['aModules'] = parent::getModuleVar('aModules');
			foreach ($this->_staticEntries['aModules'] as $key => $value) {
				$this->_staticEntries['aModules'][$key] = explode('&', $value);
			}
			foreach ( $this->_staticEntries['aYAMMEnabledModules'] as $module )
			{
				if ( array_key_exists($this->_staticEntries['aDisabledModule'], $module) )
					continue;
				$metaPath = getShopBasePath() . 'modules/' . $this->_staticEntries['aModulePaths'][$module] . '/metadata.php';
				include( $metaPath );
				foreach ( $aModule['extend'] as $class => $path )
				{
					if ( isset($this->_staticEntries['aModules'][$class]) )
					{
						if ( !in_array($path, $this->_staticEntries['aModules'][$class]) )
							$this->_staticEntries['aModules'][$class][] = $path;
					}
					else
					{
						$this->_staticEntries['aModules'][$class] = array($path);
					}
				}
			}
			foreach ($this->_staticEntries['aModules'] as $key => $value) {
				$this->_staticEntries['aModules'][$key] = implode('&', $value);
			}
        }
        
        if( isset( $this->_staticEntries ) && array_key_exists( $sModuleVarName, $this->_staticEntries ) )
        {
        	if ( $sModuleVarName === 'aDisabledModules' )
        	{
        		return array_diff(array_merge(parent::getModuleVar($sModuleVarName), $this->_staticEntries[$sModuleVarName]), $this->_staticEntries['aYAMMEnabledModules']);
        	}
        	else
        	{
				return array_merge(parent::getModuleVar($sModuleVarName), $this->_staticEntries[$sModuleVarName]);
			}
        }
        $result = parent::getModuleVar($sModuleVarName);
        
        return $result;
    }

}

