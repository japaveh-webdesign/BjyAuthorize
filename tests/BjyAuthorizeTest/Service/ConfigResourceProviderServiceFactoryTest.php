<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link           http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright      Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license        http://framework.zend.com/license/new-bsd New BSD License
 * @package        Zend_Service
 */

namespace BjyAuthorizeTest\Service;

use \PHPUnit\Framework\TestCase;
use BjyAuthorize\Service\ConfigResourceProviderServiceFactory;

/**
 * Test for {@see \BjyAuthorize\Service\ConfigResourceProviderServiceFactory}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ConfigResourceProviderServiceFactoryTest extends TestCase
{
    /**
     * @covers \BjyAuthorize\Service\ConfigResourceProviderServiceFactory::createService
     */
    public function testCreateService()
    {
        $factory          = new ConfigResourceProviderServiceFactory();
        $serviceLocator   = $this->createMock('Laminas\\ServiceManager\\ServiceLocatorInterface');
        $config           = [
            'resource_providers' => [
                'BjyAuthorize\Provider\Resource\Config' => [],
            ],
        ];

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('BjyAuthorize\Config')
            ->will($this->returnValue($config));

        $guard = $factory->createService($serviceLocator);

        $this->assertInstanceOf('BjyAuthorize\\Provider\\Resource\\Config', $guard);
    }
}
