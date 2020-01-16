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

/**
 * Test for {@see \BjyAuthorize\Service\ResourceProvidersServiceFactory}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class BaseProvidersServiceFactoryTest extends TestCase
{
    /**
     * @covers \BjyAuthorize\Service\BaseProvidersServiceFactory::createService
     */
    public function testCreateService()
    {
        $factory        = $this->getMockForAbstractClass('BjyAuthorize\\Service\\BaseProvidersServiceFactory');
        $serviceLocator = $this->createMock('Laminas\\ServiceManager\\ServiceLocatorInterface');
        $foo            = $this->createMock('BjyAuthorize\\Provider\\Resource\\ProviderInterface');
        $bar            = $this->createMock('BjyAuthorize\\Provider\\Resource\\ProviderInterface');
        $config         = [
            'providers' => [
                'foo'                         => [],
                'bar'                         => [],
                __NAMESPACE__ . '\\MockProvider' => ['option' => 'value'],
            ],
        ];

        $serviceLocator
            ->expects($this->any())
            ->method('has')
            ->will(
                $this->returnCallback(
                    function ($serviceName) {
                        return in_array($serviceName, ['foo', 'bar'], true);
                    }
                )
            );

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with($this->logicalOr('BjyAuthorize\\Config', 'foo', 'bar'))
            ->will(
                $this->returnCallback(
                    function ($serviceName) use ($foo, $bar, $config) {
                        if ('BjyAuthorize\\Config' === $serviceName) {
                            return $config;
                        }

                        if ('foo' === $serviceName) {
                            return $foo;
                        }

                        return $bar;
                    }
                )
            );

        $providers = $factory->createService($serviceLocator);

        $this->assertCount(3, $providers);
        $this->assertContains($foo, $providers);
        $this->assertContains($bar, $providers);

        $invokableProvider = array_filter(
            $providers,
            function ($item) {
                return $item instanceof MockProvider;
            }
        );

        $this->assertCount(1, $invokableProvider);

        /* @var $invokableGuard \BjyAuthorizeTest\Service\MockProvider */
        $invokableProvider = array_shift($invokableProvider);

        $this->assertInstanceOf(__NAMESPACE__ . '\\MockProvider', $invokableProvider);

        $this->assertSame(['option' => 'value'], $invokableProvider->options);
        $this->assertSame($serviceLocator, $invokableProvider->serviceLocator);
    }
}
