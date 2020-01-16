<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorize\Service;

use BjyAuthorize\Collector\RoleCollector;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible of instantiating {@see \BjyAuthorize\Collector\RoleCollector}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class RoleCollectorServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var $identityProvider ProviderInterface */
        $identityProvider = $container->get('BjyAuthorize\Provider\Identity\ProviderInterface');

        return new RoleCollector($identityProvider);
    }

    /**
     * {@inheritDoc}
     *
     * @return RoleCollector
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, RoleCollector::class);
    }
}
