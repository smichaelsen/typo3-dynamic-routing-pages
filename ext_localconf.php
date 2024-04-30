<?php

defined('TYPO3') || defined('TYPO3_MODE') || die();

if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '12.0', '<=')) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Configuration\SiteConfiguration::class] = [
        'className' => \Smic\DynamicRoutingPages\Xclass\SiteConfiguration::class,
    ];
}
