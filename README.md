YAMM
=================
Yet another metamodule, like you know it from D3 and many others.

This module will be opensource - forever!
Let's do one module together, instead of having thousand of different moduleloaders.

Features
--------
 * Moduleloading

Feature Requests
----------------
 * Module dependencies checking
 * Dependency injection container

Installation
----------

Via Composer:
 1. Add yamm/yamm to your composer.json's require section
 2. Run `composer update`
 3. Create a file "vendormetadata.php" in `modules/yamm/`
 4. Create a file `marm_yamm.config.php` in OXiDs root directory. See Section _Config File_ for further information.
 5. Activate the modul.
  
Manual:
 1. Place the whole module in your shop in `modules/yamm/`
 2. Create a file "vendormetadata.php" in `modules/yamm/`
 3. Create a file `marm_yamm.config.php` in OXiDs root directory. See Section _Config File_ for further information.
 4. Activate the modul.

Config File
-----------
The file `marm_yamm.config.php` must contain all information regarding predefined modules and settings as an associative array:

```php
<?php
$aYAMMConfig = array(
	'aYAMMEnabledModules' => array(),    // required
	'aModulePaths' => array(),           // optional
    'aYAMMDisabledModules' => array(),   // optional
    'aYAMMSpecialClassOrder' => array(), // optional
    'bYAMMBlockControl' => false,        // optional
    /* Any other keys go here */
);

```

 * `aYAMMEnabledModules` is a simple array containing the IDs of all modules to be loaded, in loading order.
 * `aModulePaths` is an associative array mapping module IDs to directories. It can be used to override OXiD own resolvence.
 * `aYAMMDisabledModules` is a simple array of containing the IDs of all modules that should be disabled. This takes precedence over OXiDs own settings, while itself beeing superseded by **aYAMMEnabledModules** .
 * `aYAMMForceOrder` allows for diverging inheritance orders set for individual classes. For each class name given as key, the extensions made by each module ID present in the value array will be moved to the end of the chain, in given order.
 * `bYAMMBlockControl` tells YAMM to block user control of inheritance order and activation of unhandled modules. Default is `false` .
 
 Any other key will be written directly into `oxConfig` .

Version
-------
The most recent release is 1.0