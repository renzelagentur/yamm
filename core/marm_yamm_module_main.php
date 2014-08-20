<?php

class marm_yamm_module_main extends marm_yamm_module_main_parent
{
	
	public function render()
	{
		$x = parent::render();
		if (defined('MARM_YAMM_TURNED_OFF'))
			return $x;
		return 'marm_yamm_module_main.tpl';
	}
	
	public function YAMMBlocksControl()
	{
		return oxUtilsObject::getInstance()->getModuleVar('bYAMMBlockControl');
	}
	
}
