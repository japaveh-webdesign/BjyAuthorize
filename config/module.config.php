<?php

use BjyAuthorize\Collector\RoleCollector;
use BjyAuthorize\Guard\Controller;
use BjyAuthorize\Guard\Route;
use BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use BjyAuthorize\Provider\Identity\ZfcUserZendDb;
use BjyAuthorize\Provider\Resource\Config;
use BjyAuthorize\Provider\Role\ObjectRepositoryProvider;
use BjyAuthorize\Provider\Role\ZendDb;
use BjyAuthorize\Service\AddAssets;
use BjyAuthorize\Service\AuthenticationIdentityProviderServiceFactory;
use BjyAuthorize\Service\Authorize;
use BjyAuthorize\Service\AuthorizeAwareServiceInitializer;
use BjyAuthorize\Service\AuthorizeFactory;
use BjyAuthorize\Service\CacheFactory;
use BjyAuthorize\Service\CacheKeyGeneratorFactory;
use BjyAuthorize\Service\ConfigResourceProviderServiceFactory;
use BjyAuthorize\Service\ConfigRoleProviderServiceFactory;
use BjyAuthorize\Service\ConfigRuleProviderServiceFactory;
use BjyAuthorize\Service\ConfigServiceFactory;
use BjyAuthorize\Service\ControllerGuardServiceFactory;
use BjyAuthorize\Service\GuardsServiceFactory;
use BjyAuthorize\Service\IdentityProviderServiceFactory;
use BjyAuthorize\Service\ObjectRepositoryRoleProviderFactory;
use BjyAuthorize\Service\ResourceProvidersServiceFactory;
use BjyAuthorize\Service\RoleCollectorServiceFactory;
use BjyAuthorize\Service\RoleProvidersServiceFactory;
use BjyAuthorize\Service\RouteGuardServiceFactory;
use BjyAuthorize\Service\RuleProvidersServiceFactory;
use BjyAuthorize\Service\UnauthorizedStrategyServiceFactory;
use BjyAuthorize\Service\UserRoleServiceFactory;
use BjyAuthorize\Service\ZendDbRoleProviderServiceFactory;
use BjyAuthorize\Service\ZfcUserZendDbIdentityProviderServiceFactory;
use BjyAuthorize\View\RedirectionStrategy;
use BjyAuthorize\View\UnauthorizedStrategy;
use Laminas\Db\Adapter\Adapter;

/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

return [
    'bjyauthorize' => [
        // default role for unauthenticated users
        'default_role' => 'guest',

        // default role for authenticated users (if using the
        // 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider' identity provider)
        'authenticated_role' => 'user',

        // identity provider service name
        'identity_provider' => ZfcUserZendDb::class,

        // Role providers to be used to load all available roles into Laminas\Permissions\Acl\Acl
        // Keys are the provider service names, values are the options to be passed to the provider
        'role_providers' => [],

        // Resource providers to be used to load all available resources into Laminas\Permissions\Acl\Acl
        // Keys are the provider service names, values are the options to be passed to the provider
        'resource_providers' => [],

        // Rule providers to be used to load all available rules into Laminas\Permissions\Acl\Acl
        // Keys are the provider service names, values are the options to be passed to the provider
        'rule_providers' => [],

        // Guard listeners to be attached to the application event manager
        'guards' => [],

        // strategy service name for the strategy listener to be used when permission-related errors are detected
        'unauthorized_strategy' => UnauthorizedStrategy::class,

        // Template name for the unauthorized strategy
        'template' => 'error/403',

        // cache options have to be compatible with Laminas\Cache\StorageFactory::factory
        'cache_options' => [
            'adapter' => [
                'name' => 'memory',
            ],
            'plugins' => [
                'serializer',
            ]
        ],

        // Key used by the cache for caching the acl
        'cache_key' => 'bjyauthorize_acl'
    ],

    'service_manager' => [
        'factories' => [
            'BjyAuthorize\Cache' => CacheFactory::class,
            'BjyAuthorize\CacheKeyGenerator' => CacheKeyGeneratorFactory::class,
            'BjyAuthorize\Config' => ConfigServiceFactory::class,
            'BjyAuthorize\Guards' => GuardsServiceFactory::class,
            'BjyAuthorize\RoleProviders' => RoleProvidersServiceFactory::class,
            'BjyAuthorize\ResourceProviders' => ResourceProvidersServiceFactory::class,
            'BjyAuthorize\RuleProviders' => RuleProvidersServiceFactory::class,
            Controller::class => ControllerGuardServiceFactory::class,
            Route::class => RouteGuardServiceFactory::class,
            \BjyAuthorize\Provider\Role\Config::class => ConfigRoleProviderServiceFactory::class,
            ZendDb::class => ZendDbRoleProviderServiceFactory::class,
            \BjyAuthorize\Provider\Rule\Config::class => ConfigRuleProviderServiceFactory::class,
            Config::class => ConfigResourceProviderServiceFactory::class,
            Authorize::class => AuthorizeFactory::class,
            AddAssets::class => 'BjyAuthorize\Service\AddAssetsFactory',
            ProviderInterface::class => IdentityProviderServiceFactory::class,
            AuthenticationIdentityProvider::class => AuthenticationIdentityProviderServiceFactory::class,
            ObjectRepositoryProvider::class => ObjectRepositoryRoleProviderFactory::class,
            RoleCollector::class => RoleCollectorServiceFactory::class,
            ZfcUserZendDb::class => ZfcUserZendDbIdentityProviderServiceFactory::class,
            UnauthorizedStrategy::class => UnauthorizedStrategyServiceFactory::class,
            'BjyAuthorize\Service\RoleDbTableGateway' => UserRoleServiceFactory::class,
        ],
        'invokables' => [
            RedirectionStrategy::class => RedirectionStrategy::class,
        ],
        'aliases' => [
            'bjyauthorize_zend_db_adapter' => Adapter::class,
        ],
        'initializers' => [
            AuthorizeAwareServiceInitializer::class => AuthorizeAwareServiceInitializer::class
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'error/403' => __DIR__ . '/../view/error/403.phtml',
            'zend-developer-tools/toolbar/bjy-authorize-role'
            => __DIR__ . '/../view/zend-developer-tools/toolbar/bjy-authorize-role.phtml',
        ],
    ],

    'zenddevelopertools' => [
        'profiler' => [
            'collectors' => [
                'bjy_authorize_role_collector' => RoleCollector::class,
            ],
        ],
        'toolbar' => [
            'entries' => [
                'bjy_authorize_role_collector' => 'zend-developer-tools/toolbar/bjy-authorize-role',
            ],
        ],
    ],
];
