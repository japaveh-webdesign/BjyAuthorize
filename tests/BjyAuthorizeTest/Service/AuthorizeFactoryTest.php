<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorizeTest\Service;

use BjyAuthorize\Service\AuthorizeFactory;
use \PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

/**
 * Test for {@see \BjyAuthorize\Service\AuthorizeFactory}
 *
 * @author Christian Bergau <cbergau86@gmail.com>
 */
class AuthorizeFactoryTest extends TestCase
{
    /**
     * @covers \BjyAuthorize\Service\AuthorizeFactory::createService
     */
    public function testCreateServiceSetCacheOptionsIfCacheIsEnabledAndAdapterOptionsAreProvided()
    {
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService('BjyAuthorize\Config', ['cache_key' => 'bjyauthorize_acl']);

        $authorizeFactory = new AuthorizeFactory();

        $authorize = $authorizeFactory->createService($serviceLocator);

        $this->assertInstanceOf('BjyAuthorize\Service\Authorize', $authorize);
    }
}
