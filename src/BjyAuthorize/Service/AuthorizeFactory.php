<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorize\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible of building the {@see \BjyAuthorize\Service\Authorize} service
 *
 * @author Ben Youngblood <bx.youngblood@gmail.com>
 */
class AuthorizeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Authorize($container->get('BjyAuthorize\Config'), $container);
    }

    /**
     * {@inheritDoc}
     *
     * @return Authorize
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, Authorize::class);
    }
}
