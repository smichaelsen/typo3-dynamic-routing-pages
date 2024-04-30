<?php

declare(strict_types=1);

namespace Smic\DynamicRoutingPages\EventListener;

use TYPO3\CMS\Core\Configuration\Event\SiteConfigurationBeforeWriteEvent;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ModifySiteConfigurationOnWriting
{
    public function __invoke(SiteConfigurationBeforeWriteEvent $event): SiteConfigurationBeforeWriteEvent
    {
        $newConfiguration = $event->getConfiguration();
        $rawConfiguration = $this->loadRawConfiguration($event->getSiteIdentifier());
        foreach ($rawConfiguration['routeEnhancers'] as $key => $enhancerConfiguration) {
            if (!isset($enhancerConfiguration['dynamicPages'])) {
                continue;
            }
            unset($newConfiguration['routeEnhancers'][$key]['limitToPages']);
        }
        $event->setConfiguration($newConfiguration);
        return $event;
    }

    protected function loadRawConfiguration(string $siteIdentifier): array
    {
        $sitesDirectory = Environment::getConfigPath() . '/sites';
        $fileName = $sitesDirectory . '/' . $siteIdentifier . '/config.yaml';
        $loader = GeneralUtility::makeInstance(YamlFileLoader::class);
        $processed = $loader->load(GeneralUtility::fixWindowsFilePath($fileName));
        return $processed;
    }
}
