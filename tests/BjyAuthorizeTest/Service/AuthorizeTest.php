<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorizeTest\Service;

use BjyAuthorize\Service\Authorize;
use \PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

/**
 * Test for {@see \BjyAuthorize\Service\Authorize}
 *
 * @author Christian Bergau <cbergau86@gmail.com>
 */
class AuthorizeTest extends TestCase
{
    /** @var  ServiceManager */
    protected $serviceLocator;

    public function setUp()
    {
        $cache = $this->getMockBuilder('Laminas\Cache\Storage\Adapter\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $cache->expects($this->any())->method('getItem');
        $cache->expects($this->any())->method('setItem');

        $serviceLocator = new ServiceManager();
        $serviceLocator->setService('BjyAuthorize\Cache', $cache);
        $serviceLocator->setService(
            'BjyAuthorize\Provider\Identity\ProviderInterface',
            $this->createMock('BjyAuthorize\Provider\Identity\ProviderInterface')
        );
        $serviceLocator->setService(
            'BjyAuthorize\RoleProviders',
            $this->createMock('BjyAuthorize\Service\RoleProvidersServiceFactory')
        );

        $serviceLocator->setService(
            'BjyAuthorize\ResourceProviders',
            $this->createMock('BjyAuthorize\Service\ResourceProvidersServiceFactory')
        );

        $serviceLocator->setService(
            'BjyAuthorize\RuleProviders',
            $this->createMock('BjyAuthorize\Service\RuleProvidersServiceFactory')
        );
        $serviceLocator->setService(
            'BjyAuthorize\Guards',
            $this->createMock('BjyAuthorize\Service\GuardsServiceFactory')
        );
        $serviceLocator->setService(
            'BjyAuthorize\CacheKeyGenerator',
            function () {
                return 'bjyauthorize-acl';
            }
        );
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @covers \BjyAuthorize\Service\Authorize::load
     */
    public function testLoadLoadsAclFromCacheAndDoesNotBuildANewAclObject()
    {
        $acl = $this->createMock('Laminas\Permissions\Acl\Acl');

        $cache = $this->getMockBuilder('Laminas\Cache\Storage\Adapter\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $cache
            ->expects($this->once())
            ->method('getItem')
            ->will(
                $this->returnCallback(
                    function ($key, & $success) use ($acl) {
                        $success = true;

                        return $acl;
                    }
                )
            );

        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'BjyAuthorize\Provider\Identity\ProviderInterface',
            $this->createMock('BjyAuthorize\Provider\Identity\ProviderInterface')
        );
        $serviceManager->setService('BjyAuthorize\Cache', $cache);
        $serviceManager->setService(
            'BjyAuthorize\CacheKeyGenerator',
            function () {
                return 'bjyauthorize-acl';
            }
        );
        $authorize = new Authorize(['cache_key' => 'bjyauthorize-acl'], $serviceManager);
        $authorize->load();

        $this->assertSame($acl, $authorize->getAcl());
    }

    /**
     * @covers \BjyAuthorize\Service\Authorize::load
     */
    public function testLoadWritesAclToCacheIfCacheIsEnabledButAclIsNotStoredInCache()
    {
        $cache = $this->getMockBuilder('Laminas\Cache\Storage\Adapter\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $cache->expects($this->once())->method('getItem');
        $cache->expects($this->once())->method('setItem');

        $serviceLocator = new ServiceManager();
        $serviceLocator->setService('BjyAuthorize\Cache', $cache);
        $serviceLocator->setService(
            'BjyAuthorize\Provider\Identity\ProviderInterface',
            $this->createMock('BjyAuthorize\Provider\Identity\ProviderInterface')
        );
        $serviceLocator->setService(
            'BjyAuthorize\RoleProviders',
            $this->createMock('BjyAuthorize\Service\RoleProvidersServiceFactory')
        );
        $serviceLocator->setService(
            'BjyAuthorize\ResourceProviders',
            $this->createMock('BjyAuthorize\Service\ResourceProvidersServiceFactory')
        );
        $serviceLocator->setService(
            'BjyAuthorize\RuleProviders',
            $this->createMock('BjyAuthorize\Service\RuleProvidersServiceFactory')
        );
        $serviceLocator->setService(
            'BjyAuthorize\Guards',
            $this->createMock('BjyAuthorize\Service\GuardsServiceFactory')
        );
        $serviceLocator->setService(
            'BjyAuthorize\CacheKeyGenerator',
            function () {
                return 'acl';
            }
        );
        $authorize = new Authorize(['cache_key' => 'acl'], $serviceLocator);
        $authorize->load();
    }


    /**
     * @group bjyoungblood/BjyAuthorize#258
     */
    public function testCanAddResourceInterfaceToLoadResource()
    {
        $serviceLocator = $this->serviceLocator;
        $serviceLocator->setAllowOverride(true);

        $resourceProviderMock = $this->getMockBuilder('BjyAuthorize\Provider\Resource\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getResources'])
            ->getMock();

        $resourceProviderMock
            ->expects($this->once())
            ->method('getResources')
            ->will(
                $this->returnValue(
                    [new \Laminas\Permissions\Acl\Resource\GenericResource('test')]
                )
            );

        $serviceLocator->setService('BjyAuthorize\Provider\Resource\Config', $resourceProviderMock);
        $serviceLocator->setService('BjyAuthorize\ResourceProviders', [$resourceProviderMock]);

        $authorize = new Authorize(['cache_key' => 'acl'], $this->serviceLocator);
        $authorize->load();

        $acl = $authorize->getAcl();

        $this->assertTrue($acl->hasResource('test'));
    }

    /**
     * @group bjyoungblood/BjyAuthorize#258
     */
    public function testCanAddTraversableResourceToLoadResource()
    {
        $serviceLocator = $this->serviceLocator;
        $serviceLocator->setAllowOverride(true);

        $resourceProviderMock = $this->getMockBuilder('BjyAuthorize\Provider\Resource\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $resourceProviderMock
            ->expects($this->once())
            ->method('getResources')
            ->will(
                $this->returnValue(
                    new \Laminas\Stdlib\ArrayObject(['test'])
                )
            );

        $serviceLocator->setService('BjyAuthorize\Provider\Resource\Config', $resourceProviderMock);
        $serviceLocator->setService('BjyAuthorize\ResourceProviders', [$resourceProviderMock]);

        $authorize = new Authorize(['cache_key' => 'acl'], $serviceLocator);

        $acl = $authorize->getAcl();

        $this->assertTrue($acl->hasResource('test'));
    }


    /**
     * @group bjyoungblood/BjyAuthorize#258
     */
    public function testCanAddNonTraversableResourceToLoadResourceThrowsInvalidArgumentException()
    {
        $this->expectException('\InvalidArgumentException');

        $serviceLocator = $this->serviceLocator;
        $serviceLocator->setAllowOverride(true);

        $resourceProviderMock = $this->getMockBuilder('BjyAuthorize\Provider\Resource\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $resourceProviderMock
            ->expects($this->once())
            ->method('getResources')
            ->will(
                $this->returnValue(
                    'test'
                )
            );

        $serviceLocator->setService('BjyAuthorize\Provider\Resource\Config', $resourceProviderMock);
        $serviceLocator->setService('BjyAuthorize\ResourceProviders', [$resourceProviderMock]);

        $authorize = new Authorize(['cache_key' => 'acl'], $this->serviceLocator);
        $authorize->load();
    }

    /**
     * @group bjyoungblood/BjyAuthorize#258
     */
    public function testCanAddTraversableRoleToLoadRole()
    {
        $serviceLocator = $this->serviceLocator;
        $serviceLocator->setAllowOverride(true);

        $roleProviderMock = $this->getMockBuilder('BjyAuthorize\Provider\Role\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $roleProviderMock
            ->expects($this->once())
            ->method('getRoles')
            ->will(
                $this->returnValue(
                    new \Laminas\Stdlib\ArrayObject([new \BjyAuthorize\Acl\Role('test')])
                )
            );

        $serviceLocator->setService('BjyAuthorize\Provider\Role\Config', $roleProviderMock);
        $serviceLocator->setService('BjyAuthorize\RoleProviders', [$roleProviderMock]);

        $authorize = new Authorize(['cache_key' => 'acl'], $this->serviceLocator);
        $authorize->load();

        $acl = $authorize->getAcl();

        $this->assertTrue($acl->hasRole('test'));
    }
}
