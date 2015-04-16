<?php
/**
 * This file is part of a yammalade GmbH project
 *
 * It is Open Source and may be redistributed.
 * For contact information please visit http://www.yammalade.de
 *
 * Version:    1.0
 * Author URI: http://www.yammalade.de
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
    'description'   => 'Yet another meta module',
    'thumbnail'     => 'yamm.jpg',
    'version'       => '1.0',
    'author'        => 'YAMM Contributors',
    'url'           => 'https://github.com/yammaladeDE/yamm',
    'extend'        => array(
        'oxconfig'          => 'yamm/yamm/core/yamm_oxconfig',
        'oxutilsobject'     => 'yamm/yamm/core/yamm_oxutilsobject',
        'oxmodule'			=> 'yamm/yamm/core/yamm_oxmodule',
        'module_list'		=> 'yamm/yamm/core/yamm_module_list',
        'module_main'		=> 'yamm/yamm/core/yamm_module_main',
        'module_sortlist'	=> 'yamm/yamm/core/yamm_module_sortlist',
    ),
    'files' => array(
		'yamm_events'            => 'yamm/yamm/core/yamm_events.php',
		'yamm_export'            => 'yamm/yamm/export.php',
		'yamm_module_metadata'   => 'yamm/yamm/core/yamm_module_metadata.php',
	),
    'templates' => array(
        'yamm_module_list.tpl'     => 'yamm/yamm/views/admin/tpl/yamm_module_list.tpl',
        'yamm_module_main.tpl'     => 'yamm/yamm/views/admin/tpl/yamm_module_main.tpl',
        'yamm_module_sortlist.tpl' => 'yamm/yamm/views/admin/tpl/yamm_module_sortlist.tpl',
        'yamm_module_metadata.tpl' => 'yamm/yamm/views/admin/tpl/yamm_module_metadata.tpl',
    ),
	'blocks' => array(
		array('template' => 'headitem.tpl', 'block' => 'admin_headitem_inccss', 'file' => 'views/admin/blocks/yamm_inccss.tpl'),
	),
	'events' => array(
		'onActivate' => 'yamm_events::activate',
		'onDeactivate' => 'yamm_events::deactivate',
	),
); 