<?php

function getShopBasePath() {
    return __DIR__ . '/../../../';
}

require_once __DIR__ . '/../../../bootstrap.php';

// Check for admin when not on CLI
if ( php_sapi_name() != 'cli' ) {
        die('Access denied');
}

$sl = oxNew('module_sortlist');
$sl->export();
