# Dynamic Routing Pages

Instead of hardcoding the `limitToPages` configuration for your route enhancers this package can automatically detect
the necessary pages for you and generate the configuration on the fly.

## Problem

Imagine the following typical routing configuration for the news plugin.

````
routeEnhancers:
  NewsPages:
    type: Extbase
    # add every page-ID that contains a News Plugin
    limitToPages:
      - 23
      - 42
      - 123
      - 242
    extension: News
    plugin: Pi1
    routes:
      - { routePath: '/{myNewsTitle}', _controller: 'News::detail', _arguments: {'myNewsTitle': 'news'} }
      - { routePath: '/{myPagination}', _controller: 'News::list', _arguments: {'myPagination': '@widget_0/currentPage'} }
      - { routePath: '/{year}/{month}', _controller: 'News::list', _arguments: {'year' : 'overwriteDemand/year', 'month' : 'overwriteDemand/month'} }
    defaultController: 'News::list'
    # ...
````

Hardcoding the page ids for your plugin routes has the major drawback that you have to adapt the configuration as soon as someone creates a new page with a plugin (which might happen in a CMS).
With the route enhancers configuration not available in the Site module this means you have to ship an updated configuration file every time an editor creates a plugin page.

## Solution

````
routeEnhancers:
  NewsPages:
    type: Extbase
    dynamicPages:
        withPlugin: news_pi1
    extension: News
    plugin: Pi1
    routes:
      - { routePath: '/{myNewsTitle}', _controller: 'News::detail', _arguments: {'myNewsTitle': 'news'} }
      - { routePath: '/{myPagination}', _controller: 'News::list', _arguments: {'myPagination': '@widget_0/currentPage'} }
      - { routePath: '/{year}/{month}', _controller: 'News::list', _arguments: {'year' : 'overwriteDemand/year', 'month' : 'overwriteDemand/month'} }
    defaultController: 'News::list'
    # ...
````

Notice the `dynamicPages` configuration. This package will populate the `limitToPages` with matching pages.

## Reference

`dynamicPages` has three possible properties.

### `withPlugin`

Can be a string or an array of `tt_content.list_type` values. Will find all pages that contain at least one of the given plugins.

### `containsModule`

Can be a string or an array of `pages.module` values. Will find all pages that have "Contains Plugin" set to one of the given values.

### `withSwitchableControllerAction`

Can be a string or an array of `switchableControllerActions` values. Will find all pages that contain plugins with the given action configured.

### `withCType`

Can be a string or an array of `withCType` values. Will find all pages that contain content element with given CType.
