<?php

class marm_yamm_module_sortlist extends marm_yamm_module_sortlist_parent
{
	
	public function render()
	{
		$x = parent::render();
		if (defined('MARM_YAMM_TURNED_OFF'))
			return $x;
		return 'marm_yamm_module_sortlist.tpl';
	}
	
	public function YAMMBlocksControl()
	{
		return oxUtilsObject::getInstance()->getModuleVar('bYAMMBlockControl');
	}
	
}
