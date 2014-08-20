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
    'id'            => 'marm/yamm',
    'title'         => ' -> marmalade :: YAMM',
    'description'   => 'Yet another meta module',
    'thumbnail'     => 'marmalade.jpg',
    'version'       => '1.0',
    'author'        => 'marmalade GmbH',
    'email'         => 'support@marmalade.de',
    'url'           => 'http://www.marmalade.de',
    'extend'        => array(
        'oxconfig'          => 'marm/yamm/core/marm_yamm_oxconfig',
        'oxutilsobject'     => 'marm/yamm/core/marm_yamm_oxutilsobject',
        'oxmodule'			=> 'marm/yamm/core/marm_yamm_oxmodule',
        'module_list'		=> 'marm/yamm/core/marm_yamm_module_list',
        'module_main'		=> 'marm/yamm/core/marm_yamm_module_main',
    ),
    'files' => array(
		'marm_yamm_events' => 'marm/yamm/core/marm_yamm_events.php',
	),
    'templates' => array(
        'marm_yamm_module_list.tpl' => 'marm/yamm/views/admin/tpl/marm_yamm_module_list.tpl',
        'marm_yamm_module_main.tpl' => 'marm/yamm/views/admin/tpl/marm_yamm_module_main.tpl',
    ),
	'blocks' => array(
		array( 'template' => 'headitem.tpl', 'block' => 'admin_headitem_inccss', 'file' => 'views/admin/blocks/marm_yamm_inccss.tpl'),
	),
	'events' => array(
		'onActivate' => 'marm_yamm_events::activate',
		'onDeactivate' => 'marm_yamm_events::deactivate',
	),
); 