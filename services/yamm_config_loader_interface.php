<?php

/**
 * Interface yamm_config_loader_interface
 *
 * Interface that defines a config loader
 */
interface yamm_config_loader_interface {

    const CONFIG_TYPE_ENBALED_MODULES       = 'enabledModules';

    const CONFIG_TYPE_DISABLED_MODULES      = 'disabledModules';

    const CONFIG_TYPE_SPECIAL_CLASS_ORDER   = 'specialClassOrder';

    const CONFIG_TYPE_MODULE_PATHS          = 'modulePaths';

    const CONFIG_TYPE_BLOCK_CONTROLL        = 'blockControll';

    /**
     * Determines wether a config could be found or not
     * @return mixed
     */
    public function configFound();

    /**
     * @param string|null $sConfigType The type of config to check for
     *
     * @return mixed
     */
    public function getConfigModificationTime($sConfigType = null);

    /**
     * Returns the enabled modules from this loaded config
     * @return array
     */
    public function getEnabledModules();

    /**
     * Returns the disabled modules from this loaded config
     * @return array
     */
    public function getDisabledModules();

    /**
     * Returns the special class order from this loaded config
     * @return array
     */
    public function getSpecialClassOrder();

    /**
     * Returns the module paths from this loaded config
     * @return array
     */
    public function getModulePaths();

    /**
     * Returns the block control from this loaded config
     * @return array
     */
    public function getBlockControl();

} 