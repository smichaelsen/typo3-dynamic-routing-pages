<?php
return [
    'frontend' => [
        'swisscom/dynamic-routing-pages/modify-site-config' => [
            'target' => \Swisscom\DynamicRoutingPages\Middleware\ModifySiteConfig::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
