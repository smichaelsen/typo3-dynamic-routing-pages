<?php
return [
    'frontend' => [
        'smic/dynamic-routing-pages/modify-site-config' => [
            'target' => \Smic\DynamicRoutingPages\Middleware\ModifySiteConfig::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
