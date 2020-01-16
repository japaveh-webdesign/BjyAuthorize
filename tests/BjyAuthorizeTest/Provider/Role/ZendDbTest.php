<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorizeTest\Provider\Role;

use BjyAuthorize\Acl\Role;
use BjyAuthorize\Provider\Role\ZendDb;
use \PHPUnit\Framework\TestCase;

/**
 * {@see \BjyAuthorize\Provider\Role\ZendDb} test
 *
 * @author Tom Oram <tom@scl.co.uk>
 */
class ZendDbTest extends TestCase
{
    /**
     * @var \BjyAuthorize\Provider\Role\ObjectRepositoryProvider
     */
    private $provider;

    /**
     * @var \Laminas\ServiceManager\ServiceLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceLocator;
    
    /**
     * @var \Laminas\Db\TableGateway\TableGateway|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tableGateway;

    /**
     * @covers \BjyAuthorize\Provider\Role\ZendDb::__construct
     */
    protected function setUp()
    {
        $this->serviceLocator = $this->createMock('Laminas\ServiceManager\ServiceLocatorInterface');
        $this->provider       = new ZendDb([], $this->serviceLocator);
        $this->tableGateway   = $this->getMockBuilder('Laminas\Db\TableGateway\TableGateway')
                                     ->disableOriginalConstructor()
                                     ->getMock();
    }

    /**
     * @covers \BjyAuthorize\Provider\Role\ZendDb::getRoles
     */
    public function testGetRoles()
    {
        $this->tableGateway->expects($this->any())->method('selectWith')->will(
            $this->returnValue(
                [
                    ['id' => 1, 'role_id' => 'guest', 'is_default' => 1, 'parent_id' => null],
                    ['id' => 2, 'role_id' => 'user', 'is_default' => 0, 'parent_id' => null],
                ]
            )
        );

        $this->serviceLocator->expects($this->any())->method('get')->will($this->returnValue($this->tableGateway));
        $provider = new ZendDb([], $this->serviceLocator);

        $this->assertEquals($provider->getRoles(), [new Role('guest'), new Role('user')]);
    }

    /**
     * @covers \BjyAuthorize\Provider\Role\ZendDb::getRoles
     */
    public function testGetRolesWithInheritance()
    {
        $this->tableGateway->expects($this->any())->method('selectWith')->will(
            $this->returnValue(
                [
                    ['id' => 1, 'role_id' => 'guest', 'is_default' => 1, 'parent_id' => null],
                    ['id' => 2, 'role_id' => 'user', 'is_default' => 0, 'parent_id' => 1],
                ]
            )
        );

        $this->serviceLocator->expects($this->any())->method('get')->will($this->returnValue($this->tableGateway));
        $provider = new ZendDb([], $this->serviceLocator);

        $this->assertEquals($provider->getRoles(), [new Role('guest'), new Role('user', 'guest')]);
    }
}
