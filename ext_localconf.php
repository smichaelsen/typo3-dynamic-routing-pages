<?php

defined('TYPO3') || defined('TYPO3_MODE') || die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Configuration\SiteConfiguration::class] = [
    'className' => \Smic\DynamicRoutingPages\Xclass\SiteConfiguration::class,
];
