<?php

function getShopBasePath() {
    return __DIR__ . '/../../../';
}

require_once __DIR__ . '/../../../bootstrap.php';

// Check for admin when not on CLI
if ( php_sapi_name() != 'cli' ) {
        $user = oxNew('oxUser');
        $user->loadAdminUser() || die('Access denied');
}


$modulePaths = array();
foreach (oxRegistry::getConfig()->getConfigParam('aModulePaths') as $key => $value) {
	if ($key)
        $modulePaths[$key] = $value;
}

function getModuleFromClassPath($class) {
    global $modulePaths;
    foreach ($modulePaths as $key => $value)
        if (strpos($class, $value) === 0)
            return $key;
    return false;
}

function deClass($a) {
    return array_slice($a, 0, -1);
}

$modules = array();
foreach (oxUtilsObject::getInstance()->getModuleVar('aModules') as $class => $classes) {
	$classes = explode('&', $classes);
    $modules[$class] = array_unique(array_reverse(array_map(getModuleFromClassPath, $classes)));
}

$disabledModules = array_filter(oxUtilsObject::getInstance()->getModuleVar('aDisabledModules'), function($b) {
    return $b != 'marm/yamm'; // yamm should never block itself
});

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
    'aYAMMEnabledModules' => array_unique($enabledModules),
    'aYAMMDisabledModules' => array_unique($disabledModules),
    'aYAMMSpecialClassOrder' => array(),
    'aModulePaths' => $modulePaths,
    'bYAMMBlockControl' => false,
);
foreach ($flat as $modules) {
	$aYAMMConfig['aYAMMSpecialClassOrder'][array_pop($modules)] = $modules;
}

// check for non-extending modules, and those which where never switched on...
$module = oxNew('oxModule');
foreach ($modulePaths as $id => $path) {
    if (in_array($id, $aYAMMConfig['aYAMMEnabledModules']) || in_array($id, $aYAMMConfig['aYAMMDisabledModules']))
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