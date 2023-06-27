<?php
declare(strict_types=1);
namespace Smic\DynamicRoutingPages\EventListener;

use Smic\DynamicRoutingPages\ConfigurationModifier;
use TYPO3\CMS\Core\Configuration\Event\SiteConfigurationLoadedEvent;

class SiteConfigurationLoadedEventListener
{
    public function __invoke(SiteConfigurationLoadedEvent $event): void
    {
        $key = $event->getSiteIdentifier();
        $newConfiguration = ConfigurationModifier::modifyConfiguration([$key => $event->getConfiguration()]);
        $event->setConfiguration($newConfiguration[$key]);
    }
}
