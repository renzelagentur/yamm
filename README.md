marmalade :: YAMM
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

How to use
----------
 1. Place the whole module in your shop in `modules/marm/`
 2. Create a file "vendormetadata.php" in `modules/marm/`
 3. Create a file `marm_yamm.config.php` in OXiDs root directory. See Section _Config File_ for further information.
 3. Activate the modul.

Config File
-----------
The file `marm_yamm.config.php` must contain all information regarding predefined modules and settings as an associative array:

```php
<?php
$aYAMMConfig = array(
	'aYAMMEnabledModules' => array(),  // required
    'aYAMMDisabledModules' => array(), // required
    'aDisabledModules' => array(),     // optional
    'bYAMMRenice' => false,            // optional
    'bYAMMBlockControl' => false,      // optional
    /* Any other keys go here */
);

```

 * `aYAMMEnabledModules` is a simple array containing the IDs of all modules to be loaded, in loading order.
 * `aModulePaths` is an associative array mapping module IDs to directories. This is used by YAMM to find all metadata files!
 * `aYAMMDisabledModules` is a simple array of containing the IDs of all modules that should be disabled. This takes precedence over OXiDs own settings, while itself beeing superseded by **aYAMMEnabledModules** .
 * `bYAMMRenice` tells YAMM wether it should keep the inheritance order set in OXiDs backend, or to enforce the order given by **aYAMMEnabledModules** . Default is `false` .
 * `bYAMMBlockControl` tells YAMM to block user control of inheritance order and activation of unhandled modules. Default is `false` . Will enforce `bYAMMRenice == true` when activated.
 
 Any other key will be written directly into `oxConfig` .

Version
-------
The most recent release is 1.0