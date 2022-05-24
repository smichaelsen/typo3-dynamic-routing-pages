<?php
declare(strict_types=1);
namespace Smic\DynamicRoutingPages;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationModifier
{
    protected static $cache = [];

    public static function modifyConfiguration(array $configuration): array
    {
        foreach ($configuration as $siteKey => $siteConfiguration) {
            $configuration[$siteKey] = self::modifySiteConfiguration($siteConfiguration);
        }
        return $configuration;
    }

    protected static function modifySiteConfiguration(array $siteConfiguration): array
    {
        if (!isset($siteConfiguration['routeEnhancers'])) {
            return $siteConfiguration;
        }

        foreach ($siteConfiguration['routeEnhancers'] as $key => $enhancerConfiguration) {
            if (!isset($enhancerConfiguration['dynamicPages'])) {
                continue;
            }
            $enhancerConfiguration['limitToPages'] = self::findDynamicPages($enhancerConfiguration['dynamicPages']);
            $siteConfiguration['routeEnhancers'][$key] = $enhancerConfiguration;
        }
        return $siteConfiguration;
    }

    protected static function findDynamicPages(array $dynamicPagesConfiguration): array
    {
        $pageUids = [];
        if (isset($dynamicPagesConfiguration['withPlugin'])) {
            $withPlugins = is_array($dynamicPagesConfiguration['withPlugin']) ? $dynamicPagesConfiguration['withPlugin'] : [$dynamicPagesConfiguration['withPlugin']];
            $withPluginsCacheKey = sha1(json_encode($withPlugins));
            self::$cache[$withPluginsCacheKey] = self::$cache[$withPluginsCacheKey] ?? self::findPagesWithPlugins($withPlugins);
            array_push($pageUids, ...self::$cache[$withPluginsCacheKey]);
        }
        if (isset($dynamicPagesConfiguration['containsModule'])) {
            $containsModules = is_array($dynamicPagesConfiguration['containsModule']) ? $dynamicPagesConfiguration['containsModule'] : [$dynamicPagesConfiguration['containsModule']];
            $containsModulesCacheKey = sha1(json_encode($containsModules));
            self::$cache[$containsModulesCacheKey] = self::$cache[$containsModulesCacheKey] ?? self::findPagesContainingModules($containsModules);
            array_push($pageUids, ...self::$cache[$containsModulesCacheKey]);
        }
        if (isset($dynamicPagesConfiguration['withSwitchableControllerAction'])) {
            $withSwitchableControllerActions = is_array($dynamicPagesConfiguration['withSwitchableControllerAction']) ? $dynamicPagesConfiguration['withSwitchableControllerAction'] : [$dynamicPagesConfiguration['withSwitchableControllerAction']];
            $withSwitchableControllerActionsCacheKey = sha1(json_encode($withSwitchableControllerActions));
            self::$cache[$withSwitchableControllerActionsCacheKey] = self::$cache[$withSwitchableControllerActionsCacheKey] ?? self::findPagesWithSwitchableControllerActions($withSwitchableControllerActions);
            array_push($pageUids, ...self::$cache[$withSwitchableControllerActionsCacheKey]);
        }
        return array_unique($pageUids);
    }

    protected static function findPagesWithPlugins(array $withPlugins): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $contentElementRecords = $queryBuilder
            ->select('pid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in('list_type', $queryBuilder->createNamedParameter($withPlugins, Connection::PARAM_STR_ARRAY))
            )
            ->execute()
            ->fetchFirstColumn();
        return $contentElementRecords;
    }

    protected static function findPagesWithSwitchableControllerActions(array $withSwitchableControllerActions): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $constraints = [];
        foreach ($withSwitchableControllerActions as $withSwitchableControllerAction) {
            $constraints[] = $queryBuilder->expr()->like('pi_flexform', $queryBuilder->createNamedParameter('%>' . htmlentities($withSwitchableControllerAction) . '<%', \PDO::PARAM_STR));
        }
        $contentElementRecords = $queryBuilder
            ->select('pid')
            ->from('tt_content')
            ->where($queryBuilder->expr()->orX(...$constraints))
            ->execute()
            ->fetchFirstColumn();
        return $contentElementRecords;
    }

    protected static function findPagesContainingModules(array $modules): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $pageRecords = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->in('module', $queryBuilder->createNamedParameter($modules, Connection::PARAM_STR_ARRAY))
            )
            ->execute()
            ->fetchFirstColumn();
        return $pageRecords;
    }
}
