<?php
declare(strict_types=1);
namespace Smic\DynamicRoutingPages\Xclass;

use Smic\DynamicRoutingPages\ConfigurationModifier;

/**
 * @see https://forge.typo3.org/issues/92778
 * The original SiteConfiguration has no possibility for extensions to modify the configuration.
 * That's why we need to xclass it.
 */
class SiteConfiguration extends \TYPO3\CMS\Core\Configuration\SiteConfiguration
{
    protected function getAllSiteConfigurationFromFiles(bool $useCache = true): array
    {
        $siteConfiguration = $useCache ? $this->cache->require($this->cacheIdentifier) : false;
        if ($siteConfiguration !== false) {
            return $siteConfiguration;
        }
        $siteConfiguration = parent::getAllSiteConfigurationFromFiles($useCache);
        $siteConfiguration = ConfigurationModifier::modifyConfiguration($siteConfiguration);
        $this->cache->set($this->cacheIdentifier, 'return ' . var_export($siteConfiguration, true) . ';');

        return $siteConfiguration;
    }
}
