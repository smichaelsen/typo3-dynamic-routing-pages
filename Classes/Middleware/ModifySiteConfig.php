<?php
declare(strict_types = 1);
namespace Swisscom\DynamicRoutingPages\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ModifySiteConfig implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Site $site */
        $site = $request->getAttribute('site', null);
        $modifiedConfiguration = $this->modifyConfiguration($site->getConfiguration());
        // The configuration of a Site is immutable so we need to instanciate a new Site with the modified config
        $modifiedSite = GeneralUtility::makeInstance(Site::class, $site->getIdentifier(), $site->getRootPageId(), $modifiedConfiguration);
        $request = $request->withAttribute('site', $modifiedSite);
        return $handler->handle($request);
    }

    protected function modifyConfiguration(array $configuration): array
    {
        if (!isset($configuration['routeEnhancers'])) {
            return $configuration;
        }

        foreach ($configuration['routeEnhancers'] as $key => $enhancerConfiguration) {
            if (!isset($enhancerConfiguration['dynamicPages'])) {
                continue;
            }
            $enhancerConfiguration['limitToPages'] = $this->findDynamicPages($enhancerConfiguration['dynamicPages']);
            unset($enhancerConfiguration['dynamicPages']);
            $configuration['routeEnhancers'][$key] = $enhancerConfiguration;
        }
        return $configuration;
    }

    protected function findDynamicPages(array $dynamicPagesConfiguration): array
    {
        $pageUids = [];
        if (isset($dynamicPagesConfiguration['withPlugin'])) {
            $withPlugins = is_array($dynamicPagesConfiguration['withPlugin']) ? $dynamicPagesConfiguration['withPlugin'] : [$dynamicPagesConfiguration['withPlugin']];
            array_push($pageUids, ...$this->findPagesWithPlugins($withPlugins));
        }
        if (isset($dynamicPagesConfiguration['containsModule'])) {
            $containsModules = is_array($dynamicPagesConfiguration['containsModule']) ? $dynamicPagesConfiguration['containsModule'] : [$dynamicPagesConfiguration['containsModule']];
            array_push($pageUids, ...$this->findPagesContainingModules($containsModules));
        }
        return $pageUids;
    }

    protected function findPagesWithPlugins(array $withPlugins): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $contentElementRecords = $queryBuilder
            ->select('pid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in('list_type', $queryBuilder->createNamedParameter($withPlugins, Connection::PARAM_STR_ARRAY))
            )
            ->execute()
            ->fetchAll();
        return array_column($contentElementRecords, 'pid');
    }

    protected function findPagesContainingModules(array $modules): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $pageRecords = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->in('module', $queryBuilder->createNamedParameter($modules, Connection::PARAM_STR_ARRAY))
            )
            ->execute()
            ->fetchAll();
        return array_column($pageRecords, 'uid');
    }
}
