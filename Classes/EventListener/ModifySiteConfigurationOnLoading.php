<?php

declare(strict_types=1);

namespace Smic\DynamicRoutingPages\EventListener;

use Smic\DynamicRoutingPages\ConfigurationModifier;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\SiteConfigurationLoadedEvent;

#[AsEventListener]
class ModifySiteConfigurationOnLoading
{
    public function __invoke(SiteConfigurationLoadedEvent $event): SiteConfigurationLoadedEvent
    {
        $siteConfiguration = $event->getConfiguration();
        $siteConfiguration = ConfigurationModifier::modifySiteConfiguration($siteConfiguration);
        $event->setConfiguration($siteConfiguration);

        return $event;
    }
}
