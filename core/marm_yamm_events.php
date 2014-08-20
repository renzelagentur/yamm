<?php

class marm_yamm_events {

    protected $_sConfigFile = 'marm_yamm.config.php';

    protected $_staticEntries = null;
	
	protected function activate()
	{
		if (defined('MARM_YAMM_INFINITY_PREVENTION'))
			return;
		define('MARM_YAMM_INFINITY_PREVENTION', TRUE);
		include( getShopBasePath() . $this->_sConfigFile );
		$oModule = oxNew('oxModule');
		foreach ( $this->_staticEntries['aYAMMEnabledModules'] as $id )
		{
			$oModule->load($id);
			$oModule->activate();
		}
		$alreadyDisabled = oxUtilsObject::getInstance()->getModuleVar('aDisabledModules');
		foreach ( $this->_staticEntries['aYAMMDisabledModules'] as $id )
		{
			if ( in_array($id, $alreadyDisabled) || in_array($id, $this->_staticEntries['aYAMMEnabledModules']) )
				continue;
			$oModule->load($id);
			if ( $oModule->isActive() )
			{
				$oModule->deactivate();
			}
		}
	}
	
	protected function deactivate()
	{
		define('MARM_YAMM_TURNED_OF', TRUE);
	}
	
	public static function __callStatic($name, $arguments)
	{
		$instance = new marm_yamm_events();
		call_user_method_array($name, $instance, $arguments);
	}
	
	
}
