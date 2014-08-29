<?php
/**
 * This file is part of a marmalade GmbH project
 *
 * It is Open Source and may be redistributed.
 * For contact information please visit http://www.marmalade.de
 *
 * Version:    1.0
 * Author URI: http://www.marmalade.de
 */


class marm_yamm_module_sortlist extends marm_yamm_module_sortlist_parent
{

    public function render()
    {
        $x = parent::render();
        if ( defined('MARM_YAMM_TURNED_OFF') )
            return $x;
        return 'marm_yamm_module_sortlist.tpl';
    }

    public function YAMMBlocksControl()
    {
        return oxUtilsObject::getInstance()->getModuleVar(marm_yamm_oxutilsobject::BLOCK_CONTROL);
    }
    
    public function export()
    {
        $modulePaths = array();
        foreach (oxRegistry::getConfig()->getConfigParam('aModulePaths') as $key => $value) {
            if ($key)
                $modulePaths[$key] = $value;
        }
        
        $getModuleFromClassPath = function ($class) use ($modulePaths) {
            foreach ($modulePaths as $key => $value)
                if (strpos($class, $value) === 0)
                    return $key;
            return false;
        };
        
        function noYAMM($b) {
            return $b != 'marm/yamm'; // yamm should never block or activate itself
        }
        
        function deClass($a) {
            return array_slice($a, 0, -1);
        }
        
        $modules = array();
        foreach (oxUtilsObject::getInstance()->getModuleVar('aModules') as $class => $classes) {
            $classes = explode('&', $classes);
            $modules[$class] = array_unique(array_map($getModuleFromClassPath, $classes));
        }
        
        $disabledModules = oxUtilsObject::getInstance()->getModuleVar('aDisabledModules');
        
        $flat = array();
        foreach ($modules as $key => $value) {
            $value[] = $key;
            $value = array_diff($value, $disabledModules);
            $flat[] = $value;
        }
        
        usort($flat, function($a, $b) {
            return count($b) - count($a);
        });
        $enabledModules = deClass(array_shift($flat));
        for ($i = count($flat)-1; $i > -1; $i--) {
                // copy all module ids still missing from the main array
                foreach (deClass($flat[$i]) as $module) {
                    if (!in_array($module, $enabledModules))
                        $enabledModules[] = $module;
                }
                // create an array with all ids both in the current list and in the main array,
                // in the same order as in the latter
                $shared = array_values(array_filter($enabledModules, function($m) use ($flat, $i) {
                    return in_array($m, $flat[$i]);
                }));
                
                // we can kill list which take the same order as the main array
                if ($shared == deClass($flat[$i]))
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
            'aYAMMEnabledModules' => array_unique(array_filter($enabledModules, noYAMM)),
            'aYAMMDisabledModules' => array_unique(array_filter($disabledModules, noYAMM)),
            'aYAMMSpecialClassOrder' => array(),
            'aModulePaths' => $modulePaths,
            'bYAMMBlockControl' => false,
        );
        foreach ($flat as $modules) {
            $aYAMMConfig['aYAMMSpecialClassOrder'][array_pop($modules)] = array_filter($modules, noYAMM);
        }
        
        // check for non-extending modules, and those which where never switched on...
        $module = oxNew('oxModule');
        foreach ($modulePaths as $id => $path) {
            if (in_array($id, $aYAMMConfig['aYAMMEnabledModules']) || in_array($id, $aYAMMConfig['aYAMMDisabledModules']) || $id == 'marm/yamm')
                continue;
            $module->load($id);
            if ($module->isActive())
                $aYAMMConfig['aYAMMEnabledModules'][] = $id;
            else
                $aYAMMConfig['aYAMMDisabledModules'][] = $id;
        }
        
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
        
        $output = ob_get_clean();
        
        if ( php_sapi_name() == 'cli' )
            echo $output;
        else
            highlight_string($output);
        
        die();
    }

}
