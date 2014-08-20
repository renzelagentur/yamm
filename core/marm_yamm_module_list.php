<?php

class marm_yamm_module_list extends marm_yamm_module_list_parent {
	public function render() {
		$x = parent::render();
		if (defined('MARM_YAMM_TURNED_OF'))
			return $x;
		return 'marm_yamm_module_list.tpl';
	}
}
