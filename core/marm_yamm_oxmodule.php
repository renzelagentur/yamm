<?php

class marm_yamm_oxmodule extends marm_yamm_oxmodule_parent {
	
	public function isEnabledByYAMM() {
		return in_array($this->getId(), oxUtilsObject::getInstance()->getModuleVar('aYAMMEnabledModules'));
	}
	public function isDisabledByYAMM() {
		return in_array($this->getId(), oxUtilsObject::getInstance()->getModuleVar('aYAMMDisabledModules'));
	}

}
