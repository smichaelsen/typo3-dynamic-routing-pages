<?php
declare(strict_types=1);
namespace Smic\DynamicRoutingPages\Xclass;

use Smic\DynamicRoutingPages\ConfigurationModifier;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @see https://forge.typo3.org/issues/92778
 * The original SiteConfiguration has no possibility for extensions to modify the configuration.
 * That's why we need to xclass it.
 */
class SiteConfiguration extends \TYPO3\CMS\Core\Configuration\SiteConfiguration
{
    protected PhpFrontend $cache;

    public function __construct(string $configPath)
    {
        parent::__construct($configPath);
        if (!isset($this->cache)) {
            $this->cache = GeneralUtility::getContainer()->get('cache.core');
        }
    }

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

    public function write(string $siteIdentifier, array $configuration, bool $protectPlaceholders = false): void
    {
        if (!isset($configuration['routeEnhancers'])) {
            parent::write($siteIdentifier, $configuration, $protectPlaceholders);
            return;
        }

        foreach ($configuration['routeEnhancers'] as $key => $enhancerConfiguration) {
            if (!isset($enhancerConfiguration['dynamicPages'])) {
                continue;
            }
            unset($enhancerConfiguration['limitToPages']);
            $configuration['routeEnhancers'][$key] = $enhancerConfiguration;
        }
        parent::write($siteIdentifier, $configuration, $protectPlaceholders);
    }
}
