<?php

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
     * Used to override the parent Shop ID to use for inherited configs
     * @var int
     */
    private $_iOverwriteParent = null;


    /**
     * @param bool $blInheritConfigFromParent   Should the generated config inherit from the parent Shop, if one exists?
     * @param bool $blExportDisabledModules     Should disabled modules be written to the generated config?
     * @param bool $blExportClassOrder          Shold the ordering of classes be wirtten to the generated config?
     * @param int $iOverwriteParent             Used to overwrite the Parent Shop ID used for generating inherited configs
     */
    public function __construct($blInheritConfigFromParent = true, $blExportDisabledModules = true, $blExportClassOrder = true, $iOverwriteParent = null) {
        $this->_blInheritConfigFromParent   = $blInheritConfigFromParent;
        $this->_blExportDisabledModules     = $blExportDisabledModules;
        $this->_blExportClassOrder          = $blExportClassOrder;
        $this->_iOverwriteParent            = $iOverwriteParent;
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
        $aYAMMConfig = $this->buildYammConfig($shopId);

        if ($this->_blInheritConfigFromParent) {
            list($iParentShopId, $aYAMMConfig) = $this->buildInheritedConfigArray($shopId, $aYAMMConfig);

            // When the parent shop ID is overwritten by the use, we need to check wether the current ShopID is used as parent for others
            if ($iParentShopId != $shopId) {
                return $this->generateConfig($aYAMMConfig, $iParentShopId);
            } else {
                return $this->generateConfig($aYAMMConfig);
            }
        } else {
            return $this->generateConfig($aYAMMConfig);
        }

    }

    /**
     * Generates a string, that represents the config of a YAMM Config file, which can then be passed to the user to use
     * as they wish
     *
     * @param $aYAMMConfig
     * @param $iParentId
     */
    private function generateConfig($aYAMMConfig, $iParentId = null)
    {
        if (is_null($iParentId) || $iParentId <= 0) {
            return $this->generateIndependentConfig($aYAMMConfig);
        } else {
            return $this->generateExtendedConfig($aYAMMConfig, $iParentId);
        }
    }

    /**
     * Returns the parent ShopId of a Shop
     *
     * @param $iShopId The shop ID of the shop we want to get the parent Shop ID from
     *
     * @return int
     */
    private function getParentShopId($iShopId) {
        if ($this->_iOverwriteParent !== null) {
            return $this->_iOverwriteParent;
        }

        $sQuery = sprintf('SELECT oxparentid FROM oxshops WHERE OXID = %d;', (int) $iShopId);
        return (int) oxDb::getDb()->getOne($sQuery);
    }

    /**
     * Builds the YAMM Config array
     *
     * @param $shopId
     *
     * @return array
     */
    private function buildYammConfig($shopId)
    {
        /** @var yamm_module_vars $yamm_module_vars */
        $yamm_module_vars = oxNew('yamm_module_vars', oxDb::getDb());

        $modulePaths = array();
        foreach ($yamm_module_vars->getModulePaths($shopId) as $key => $value) {
            if ($key)
                $modulePaths[$key] = $value;
        }

        $getModuleFromClassPath = function ($class) use ($modulePaths) {
            foreach ($modulePaths as $key => $value) {
                if (strpos($class, $value) === 0)
                    return $key;
            }

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

        usort(
            $flat, function ($a, $b) {
                return count($b) - count($a);
            }
        );
        $enabledModules = $this->deClass(array_shift($flat));
        for ($i = count($flat) - 1; $i > -1; $i--) {
            // copy all module ids still missing from the main array
            foreach ($this->deClass($flat[$i]) as $module) {
                if (!in_array($module, $enabledModules))
                    $enabledModules[] = $module;
            }
            // create an array with all ids both in the current list and in the main array,
            // in the same order as in the latter
            $shared = array_values(
                array_filter(
                    $enabledModules, function ($m) use ($flat, $i) {
                        return in_array($m, $flat[$i]);
                    }
                )
            );

            // we can kill list which take the same order as the main array
            if ($shared == $this->deClass($flat[$i]))
                unset($flat[$i]);
            else {
                // for all others we find the point of divergence and cut the list down
                for ($j = 0; $j < count($shared); $j++) {
                    if ($shared[$j] != $flat[$i][$j])
                        break;
                }
                $flat[$i] = array_merge(array_slice($flat[$i], $j, -1), array_slice($flat[$i], -1));
            }
        }

        $aYAMMConfig = array(
            'aYAMMEnabledModules'    => array_unique(array_filter($enabledModules, array($this, 'noYAMM'))),
            'aYAMMDisabledModules'   => $this->_blExportDisabledModules ? array_unique(array_filter($disabledModules, array($this, 'noYAMM'))) : array(),
            'aYAMMSpecialClassOrder' => array(),
            'aModulePaths'           => $modulePaths,
            'bYAMMBlockControl'      => false,
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

        return $aYAMMConfig;
    }

    /**
     * Generates a clean, new YAMM Config file
     *
     * @param $aYAMMConfig
     *
     * @return string
     */
    private function generateIndependentConfig($aYAMMConfig)
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

    /**
     * Generates the content for a config file that extends the parent config
     *
     * @param $aYAMMConfig
     * @param $iParentId
     *
     * @return string
     */
    private function generateExtendedConfig($aYAMMConfig, $iParentId)
    {
        ob_start();

        echo <<<EOT
<?php
/*
 * YAMM Config generated from existing setup.
 * Please check completeness after activating this configuration.
 */

require __DIR__ . '/../$iParentId/yamm.config.php';\n

EOT;
        foreach ($aYAMMConfig['aYAMMEnabledModules'] as $sDeleteModule) {
            echo '$aYAMMConfig[\'aYAMMEnabledModules\'][] = \'' . $sDeleteModule . '\';' . "\n";
        }
        echo "\n";

        foreach ($aYAMMConfig['aYAMMDeleteEnabledModules'] as $sDeleteModule) {
            echo 'unset($aYAMMConfig[\'aYAMMEnabledModules\'][array_search(\'' . $sDeleteModule . '\', $aYAMMConfig[\'aYAMMEnabledModules\'])]);' . "\n";
        }
        echo "\n";

        foreach ($aYAMMConfig['aYAMMDisabledModules'] as $sDisabledModule) {
            echo '$aYAMMConfig[\'aYAMMDisabledModules\'][] = \'' . $sDisabledModule . '\';' . "\n";
        }

        echo "\n";
        echo '$aYAMMConfig[\'aYAMMSpecialClassOrder\'] = ' . var_export($aYAMMConfig['aYAMMSpecialClassOrder'], true) . ";\n";
        echo "\n";
        echo '$aYAMMConfig[\'aModulePaths\'] = ' . var_export($aYAMMConfig['aModulePaths'], true) . ";\n";
        echo "\n";
        echo '$aYAMMConfig[\'bYAMMBlockControl\'] = ' . var_export($aYAMMConfig['bYAMMBlockControl'], true). ";\n";

        return ob_get_clean();
    }

    /**
     * Builds a YAMM config array, that only contains differences in regards to the parent config
     *
     * @param $shopId
     * @param $aYAMMConfig
     *
     * @return array
     */
    private function buildInheritedConfigArray($shopId, $aYAMMConfig)
    {
        $iParentShopId = $this->getParentShopId($shopId);
        if ($iParentShopId > 0 && $iParentShopId != $shopId) {
            $aParentYAMMConfig = $this->buildYammConfig($iParentShopId);

            $aNewConfig = $aYAMMConfig;
            $aNewConfig['aYAMMEnabledModules'] = array_diff($aYAMMConfig['aYAMMEnabledModules'], $aParentYAMMConfig['aYAMMEnabledModules']);
            $aNewConfig['aYAMMDeleteEnabledModules'] = array_diff($aParentYAMMConfig['aYAMMEnabledModules'], $aYAMMConfig['aYAMMEnabledModules']);

            $aYAMMConfig = $aNewConfig;

        }

        return array($iParentShopId, $aYAMMConfig);
    }

} 