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
		$this->_staticEntries = $aYAMMConfig;
		$oModule = oxNew('oxModule');
		foreach ( $this->_staticEntries['aYAMMEnabledModules'] as $id )
		{
			$oModule->load($id);
			$oModule->activate();
		}
		$toDisable = array_diff(
			$this->_staticEntries['aYAMMDisabledModules'],
			oxUtilsObject::getInstance()->getModuleVar('aDisabledModules'),
			$this->_staticEntries['aYAMMEnabledModules']
		);
		foreach ( $toDisable as $id )
		{
			$oModule->load($id);
			if ( $oModule->isActive() )
			{
				$oModule->deactivate();
			}
		}
	}
	
	protected function deactivate()
	{
		define('MARM_YAMM_TURNED_OFF', TRUE);
	}
	
	public static function __callStatic($name, $arguments)
	{
		$instance = new marm_yamm_events();
		call_user_method_array($name, $instance, $arguments);
	}
	
	
}
