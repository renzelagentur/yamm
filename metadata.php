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

/**
 * Metadata version
 */
$sMetadataVersion = '1.1';

/**
 * Module information
 */
$aModule = array(
    'id'            => 'yamm/yamm',
    'title'         => ':: YAMM',
    'description'   => 'Yet another meta module -  Oxid Module Management Extension',
    'version'       => '1.1',
    'author'        => 'marmalde GmbH & YAMM Contributors',
    'url'           => 'https://github.com/marmaladeDE/yammm',
    'extend'        => array(
        'oxconfig'          => 'yamm/yamm/core/yamm_oxconfig',
        'oxutilsobject'     => 'yamm/yamm/core/yamm_oxutilsobject',
        'oxmodule'			=> 'yamm/yamm/core/yamm_oxmodule',
        'module_list'		=> 'yamm/yamm/controller/yamm_module_list',
        'module_main'		=> 'yamm/yamm/controller/yamm_module_main',
        'module_sortlist'	=> 'yamm/yamm/controller/yamm_module_sortlist',
    ),
    'files' => array(
		'yamm_events'            => 'yamm/yamm/core/yamm_events.php',
		'yamm_export'            => 'yamm/yamm/core/yamm_export.php',
		'yamm_module_metadata'   => 'yamm/yamm/controller/yamm_module_metadata.php',
		'yamm_module_cleanup'   => 'yamm/yamm/services/yamm_module_cleanup.php',
		'yamm_exporter'   => 'yamm/yamm/services/yamm_exporter.php',
		'yamm_module_vars'   => 'yamm/yamm/services/yamm_module_vars.php',
		'yamm_config_loader_php'   => 'yamm/yamm/services/yamm_config_loader_php.php',
		'yamm_config_loader_csv'   => 'yamm/yamm/services/yamm_config_loader_csv.php',
		'yamm_invalid_argument_exception'   => 'yamm/yamm/exception/yamm_invalid_argument_exception.php',
		'yamm_config_not_found_exception'   => 'yamm/yamm/exception/yamm_config_not_found_exception.php',

	),
    'templates' => array(
        'yamm_module_list.tpl'     => 'yamm/yamm/views/admin/tpl/yamm_module_list.tpl',
        'yamm_module_main.tpl'     => 'yamm/yamm/views/admin/tpl/yamm_module_main.tpl',
        'yamm_module_sortlist.tpl' => 'yamm/yamm/views/admin/tpl/yamm_module_sortlist.tpl',
        'yamm_module_metadata.tpl' => 'yamm/yamm/views/admin/tpl/yamm_module_metadata.tpl',
        'yamm_export.tpl' => 'yamm/yamm/views/admin/tpl/yamm_export.tpl'
    ),
	'blocks' => array(
		array('template' => 'headitem.tpl', 'block' => 'admin_headitem_inccss', 'file' => 'views/admin/blocks/yamm_inccss.tpl'),
	),
	'events' => array(
		'onActivate' => 'yamm_events::activate',
		'onDeactivate' => 'yamm_events::deactivate',
	),
); 