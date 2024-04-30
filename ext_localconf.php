<?php

use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') || die();

if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 12) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][SiteConfiguration::class] = [
        'className' => \Smic\DynamicRoutingPages\Xclass\SiteConfiguration::class,
    ];
}
