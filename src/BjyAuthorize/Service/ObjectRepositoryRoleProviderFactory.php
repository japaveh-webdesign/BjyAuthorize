<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorize\Service;

use BjyAuthorize\Exception\InvalidArgumentException;
use BjyAuthorize\Provider\Role\ObjectRepositoryProvider;
use Doctrine\Common\Persistence\ObjectManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible of instantiating {@see \BjyAuthorize\Provider\Role\ObjectRepositoryProvider}
 *
 * @author Tom Oram <tom@scl.co.uk>
 * @author Jérémy Huet <jeremy.huet@gmail.com>
 */
class ObjectRepositoryRoleProviderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('BjyAuthorize\Config');

        if (!isset($config['role_providers']['BjyAuthorize\Provider\Role\ObjectRepositoryProvider'])) {
            throw new InvalidArgumentException(
                'Config for "BjyAuthorize\Provider\Role\ObjectRepositoryProvider" not set'
            );
        }

        $providerConfig = $config['role_providers']['BjyAuthorize\Provider\Role\ObjectRepositoryProvider'];

        if (!isset($providerConfig['role_entity_class'])) {
            throw new InvalidArgumentException('role_entity_class not set in the bjyauthorize role_providers config.');
        }

        if (!isset($providerConfig['object_manager'])) {
            throw new InvalidArgumentException('object_manager not set in the bjyauthorize role_providers config.');
        }

        /* @var $objectManager ObjectManager */
        $objectManager = $container->get($providerConfig['object_manager']);

        return new ObjectRepositoryProvider($objectManager->getRepository($providerConfig['role_entity_class']));
    }

    /**
     * {@inheritDoc}
     *
     * @return ObjectRepositoryProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, ObjectRepositoryProvider::class);
    }
}
