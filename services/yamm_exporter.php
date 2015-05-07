<?php

use services\yamm_module_vars;

class yamm_exporter {

    /**
     * Should the config be generated as config inherited by it's parent shop's config?
     * @var bool
     */
    private $_blInheritConfigFromParent = true;

    /**
     * Should the export generated aYAMMDisabledModules ?
     * @var bool
     */
    private $_blExportDisabledModules = true;

    /**
     * Should the Export generated aYAMMSpecialClassOrder ?
     * @var bool
     */
    private $_blExportClassOrder = true;


    /**
     * @param bool $blInheritConfigFromParent   Should the generated config inherit from the parent Shop, if one exists?
     * @param bool $blExportDisabledModules     Should disabled modules be written to the generated config?
     * @param bool $blExportClassOrder          Shold the ordering of classes be wirtten to the generated config?
     */
    public function __construct($blInheritConfigFromParent = true, $blExportDisabledModules = true, $blExportClassOrder = true) {
        $this->_blInheritConfigFromParent   = $blInheritConfigFromParent;
        $this->_blExportDisabledModules     = $blExportDisabledModules;
        $this->_blExportClassOrder          = $blExportClassOrder;
    }

    /**
     * Checks if the given module ID is YAMM's
     * @param $b
     *
     * @return bool
     */
    private function noYAMM($b) {
        return $b != 'yamm/yamm'; // yamm should never block or activate itself
    }

    /**
     * Legacy code artifact, not sure what exactly it is good for
     * @param $a
     *
     * @return array
     */
    private function deClass($a) {
        return array_slice($a, 0, -1);
    }

    /**
     * Handles all config generation
     */
    public function export($shopId)
    {
        /** @var yamm_module_vars $yamm_module_vars */
        $yamm_module_vars = oxNew('yamm_module_vars', oxDb::getDb());

        $modulePaths = array();
        foreach ($yamm_module_vars->getModulePaths($shopId) as $key => $value) {
            if ($key)
                $modulePaths[$key] = $value;
        }

        $getModuleFromClassPath = function ($class) use ($modulePaths) {
            foreach ($modulePaths as $key => $value)
                if (strpos($class, $value) === 0)
                    return $key;
            return false;
        };

        $modules = array();
        foreach ($yamm_module_vars->getModules($shopId) as $class => $classes) {
            $classes = explode('&', $classes);
            $modules[$class] = array_unique(array_map($getModuleFromClassPath, $classes));
        }

        $disabledModules = $yamm_module_vars->getDisabledModules($shopId);

        $flat = array();
        foreach ($modules as $key => $value) {
            $value[] = $key;
            $value = array_diff($value, $disabledModules);
            $flat[] = $value;
        }

        usort($flat, function($a, $b) {
                return count($b) - count($a);
            });
        $enabledModules = $this->deClass(array_shift($flat));
        for ($i = count($flat)-1; $i > -1; $i--) {
            // copy all module ids still missing from the main array
            foreach ($this->deClass($flat[$i]) as $module) {
                if (!in_array($module, $enabledModules))
                    $enabledModules[] = $module;
            }
            // create an array with all ids both in the current list and in the main array,
            // in the same order as in the latter
            $shared = array_values(array_filter($enabledModules, function($m) use ($flat, $i) {
                        return in_array($m, $flat[$i]);
                    }));

            // we can kill list which take the same order as the main array
            if ($shared == $this->deClass($flat[$i]))
                unset($flat[$i]);
            else {
                // for all others we find the point of divergence and cut the list down
                for ($j = 0; $j < count($shared); $j++)
                    if ($shared[$j] != $flat[$i][$j])
                        break;
                $flat[$i] = array_merge(array_slice($flat[$i], $j, -1), array_slice($flat[$i], -1));
            }
        }

        $aYAMMConfig = array(
            'aYAMMEnabledModules' => array_unique(array_filter($enabledModules, array($this, 'noYAMM'))),
            'aYAMMDisabledModules' => $this->_blExportDisabledModules ? array_unique(array_filter($disabledModules, array($this, 'noYAMM'))) : array(),
            'aYAMMSpecialClassOrder' => array(),
            'aModulePaths' => $modulePaths,
            'bYAMMBlockControl' => false,
        );

        if ($this->_blExportClassOrder) {
            foreach ($flat as $modules) {
                $aYAMMConfig['aYAMMSpecialClassOrder'][array_pop($modules)] = array_filter($modules, array($this, 'noYAMM'));
            }
        }

        // check for non-extending modules, and those which where never switched on...
        $module = oxNew('oxModule');
        foreach ($modulePaths as $id => $path) {
            if (in_array($id, $aYAMMConfig['aYAMMEnabledModules']) || in_array($id, $aYAMMConfig['aYAMMDisabledModules']) || $id == 'yamm/yamm')
                continue;
            $module->load($id);
            if ($module->isActive()) {
                $aYAMMConfig['aYAMMEnabledModules'][] = $id;
            } elseif ($this->_blExportDisabledModules) {
                $aYAMMConfig['aYAMMDisabledModules'][] = $id;
            }
        }

        if ($this->_blInheritConfigFromParent) {

        }

        return $this->generateConfig($aYAMMConfig, $this->_blInheritConfigFromParent);
    }

    /**
     * Generates a string, that represents the config of a YAMM Config file, which can then be passed to the user to use
     * as they wish
     *
     * @param $aYAMMConfig
     * @param $blExtending
     */
    private function generateConfig($aYAMMConfig, $blExtending = false)
    {
        ob_start();
        var_export($aYAMMConfig);
        $export = ob_get_clean();

        ob_start();

        echo <<<EOT
<?php
/*
 * YAMM Config generated from existing setup.
 * Please check completeness after activating this configuration.
 */

\$aYAMMConfig = {$export};

EOT;

        return ob_get_clean();


    }

} 