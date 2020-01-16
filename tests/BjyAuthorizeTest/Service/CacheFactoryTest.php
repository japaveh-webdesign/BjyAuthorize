<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorizeTest\Service;

use BjyAuthorize\Service\CacheFactory;
use \PHPUnit\Framework\TestCase;

/**
 * PHPUnit tests for {@see \BjyAuthorize\Service\CacheFactory}
 *
 * @author Christian Bergau <cbergau86@gmail.com>
 */
class CacheFactoryTest extends TestCase
{
    /**
     * @covers \BjyAuthorize\Service\CacheFactory::createService
     */
    public function testCreateService()
    {
        $serviceLocator = $this->createMock('Laminas\\ServiceManager\\ServiceLocatorInterface');
        $config         = [
            'cache_options' => [
                'adapter'   => [
                    'name' => 'memory',
                ],
                'plugins'   => [
                    'serializer',
                ]
            ]
        ];

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('BjyAuthorize\Config')
            ->will($this->returnValue($config));

        $factory = new CacheFactory();

        $this->assertInstanceOf('Laminas\Cache\Storage\Adapter\Memory', $factory->createService($serviceLocator));
    }
}
