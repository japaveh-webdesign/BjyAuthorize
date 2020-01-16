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
use BjyAuthorize\Service\IdentityProviderServiceFactory;

/**
 * Test for {@see \BjyAuthorize\Service\IdentityProviderServiceFactory}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class IdentityProviderServiceFactoryTest extends TestCase
{
    /**
     * @covers \BjyAuthorize\Service\IdentityProviderServiceFactory::createService
     */
    public function testCreateService()
    {
        $factory          = new IdentityProviderServiceFactory();
        $serviceLocator   = $this->createMock('Laminas\\ServiceManager\\ServiceLocatorInterface');
        $identityProvider = $this->createMock('BjyAuthorize\\Provider\\Identity\\ProviderInterface');
        $config           = ['identity_provider' => 'foo'];

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with($this->logicalOr('BjyAuthorize\\Config', 'foo'))
            ->will(
                $this->returnCallback(
                    function ($serviceName) use ($identityProvider, $config) {
                        if ('BjyAuthorize\\Config' === $serviceName) {
                            return $config;
                        }

                        return $identityProvider;
                    }
                )
            );

        $this->assertSame($identityProvider, $factory->createService($serviceLocator));
    }
}
