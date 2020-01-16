<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorize\Service;

use BjyAuthorize\Provider\Identity\ZfcUserZendDb;
use Interop\Container\ContainerInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use ZfcUser\Service\User;

/**
 * Factory responsible of instantiating {@see \BjyAuthorize\Provider\Identity\ZfcUserZendDb}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ZfcUserZendDbIdentityProviderServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var $tableGateway TableGateway */
        $tableGateway = new TableGateway('user_role_linker', $container->get('zfcuser_zend_db_adapter'));
        /* @var $userService User */
        $userService = $container->get('zfcuser_user_service');
        $config = $container->get('BjyAuthorize\Config');

        $provider = new ZfcUserZendDb($tableGateway, $userService);

        $provider->setDefaultRole($config['default_role']);

        return $provider;
    }

    /**
     * {@inheritDoc}
     *
     * @return ZfcUserZendDb
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, ZfcUserZendDb::class);
    }
}
