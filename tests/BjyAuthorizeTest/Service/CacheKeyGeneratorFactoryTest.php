<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorizeTest\Service;

use BjyAuthorize\Service\CacheKeyGeneratorFactory;
use \PHPUnit\Framework\TestCase;

/**
 * PHPUnit tests for {@see \BjyAuthorize\Service\CacheKeyGeneratorFactory}
 *
 * @author Steve Rhoades <sedonami@gmail.com>
 */
class CacheKeyGeneratorFactoryTest extends TestCase
{
    /**
     * @covers \BjyAuthorize\Service\CacheKeyGeneratorFactory::createService
     */
    public function testCreateServiceReturnsDefaultCallable()
    {
        $serviceLocator = $this->createMock('Laminas\\ServiceManager\\ServiceLocatorInterface');
        $config         = [];

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('BjyAuthorize\Config')
            ->will($this->returnValue($config));

        $factory = new CacheKeyGeneratorFactory();

        $cacheKeyGenerator = $factory->createService($serviceLocator);
        $this->assertTrue(is_callable($cacheKeyGenerator));
        $this->assertEquals('bjyauthorize_acl', $cacheKeyGenerator());
    }

    /**
     * @covers \BjyAuthorize\Service\CacheKeyGeneratorFactory::createService
     */
    public function testCreateServiceReturnsCacheKeyGeneratorCallable()
    {
        $serviceLocator = $this->createMock('Laminas\\ServiceManager\\ServiceLocatorInterface');
        $config         = [
            'cache_key' => 'some_new_value'
        ];

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('BjyAuthorize\Config')
            ->will($this->returnValue($config));

        $factory = new CacheKeyGeneratorFactory();

        $cacheKeyGenerator = $factory->createService($serviceLocator);
        $this->assertTrue(is_callable($cacheKeyGenerator));
        $this->assertEquals('some_new_value', $cacheKeyGenerator());
    }
}
