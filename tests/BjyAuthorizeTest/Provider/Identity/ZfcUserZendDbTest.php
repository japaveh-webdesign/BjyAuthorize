<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorizeTest\Provider\Identity;

use \PHPUnit\Framework\TestCase;
use BjyAuthorize\Provider\Identity\ZfcUserZendDb;

/**
 * {@see \BjyAuthorize\Provider\Identity\ZfcUserZendDb} test
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ZfcUserZendDbTest extends TestCase
{
    /**
     * @var \Laminas\Authentication\AuthenticationService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authService;

    /**
     * @var \ZfcUser\Service\User|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $userService;

    /**
     * @var \Laminas\Db\TableGateway\TableGateway|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tableGateway;

    /**
     * @var \BjyAuthorize\Provider\Identity\ZfcUserZendDb
     */
    protected $provider;

    /**
     * {@inheritDoc}
     *
     * @covers \BjyAuthorize\Provider\Identity\ZfcUserZendDb::__construct
     */
    public function setUp()
    {
        $this->authService  = $this->createMock('Laminas\Authentication\AuthenticationService');
        $this->userService  = $this->getMockBuilder('ZfcUser\Service\User')->setMethods(['getAuthService'])->getMock();
        $this->tableGateway = $this->getMockBuilder('Laminas\Db\TableGateway\TableGateway')->setMethods([])->disableOriginalConstructor()->getMock();

        $this
            ->userService
            ->expects($this->any())
            ->method('getAuthService')
            ->will($this->returnValue($this->authService));

        $this->provider = new ZfcUserZendDb($this->tableGateway, $this->userService);
    }

    /**
     * @covers \BjyAuthorize\Provider\Identity\ZfcUserZendDb::getIdentityRoles
     * @covers \BjyAuthorize\Provider\Identity\ZfcUserZendDb::setDefaultRole
     */
    public function testGetIdentityRolesWithNoAuthIdentity()
    {
        $this->provider->setDefaultRole('test-default');

        $this->assertSame(['test-default'], $this->provider->getIdentityRoles());
    }

    /**
     * @covers \BjyAuthorize\Provider\Identity\ZfcUserZendDb::getIdentityRoles
     */
    public function testSetGetDefaultRole()
    {
        $this->provider->setDefaultRole('test');
        $this->assertSame('test', $this->provider->getDefaultRole());

        $role = $this->createMock('Laminas\\Permissions\\Acl\\Role\\RoleInterface');
        $this->provider->setDefaultRole($role);
        $this->assertSame($role, $this->provider->getDefaultRole());

        $this->expectException('BjyAuthorize\\Exception\\InvalidRoleException');
        $this->provider->setDefaultRole(false);
    }

    /**
     * @covers \BjyAuthorize\Provider\Identity\ZfcUserZendDb::getIdentityRoles
     */
    public function testGetIdentityRoles()
    {
        $roles = $this->provider->getIdentityRoles();
        $this->assertEquals($roles, [null]);
    }
}
