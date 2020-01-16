<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorizeTest\Guard;

use BjyAuthorize\Exception\UnAuthorizedException;
use \PHPUnit\Framework\TestCase;
use BjyAuthorize\Guard\Controller;
use Laminas\Console\Request;
use Laminas\Mvc\MvcEvent;

/**
 * Controller Guard test
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ControllerTest extends TestCase
{
    /**
     * @var \Laminas\ServiceManager\ServiceLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceLocator;

    /**
     * @var \BjyAuthorize\Service\Authorize|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorize;

    /**
     * @var Controller
     */
    protected $controllerGuard;

    /**
     * {@inheritDoc}
     *
     * @covers \BjyAuthorize\Guard\Controller::__construct
     */
    public function setUp()
    {
        parent::setUp();

        $this->serviceLocator  = $locator = $this->createMock('Laminas\\ServiceManager\\ServiceLocatorInterface');
        $this->authorize = $authorize = $this->getMockBuilder('BjyAuthorize\\Service\\Authorize')->disableOriginalConstructor()->getMock();
        $this->controllerGuard = new Controller([], $this->serviceLocator);

        $this
            ->serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('BjyAuthorize\\Service\\Authorize')
            ->will($this->returnValue($authorize));
    }

    /**
     * @covers \BjyAuthorize\Guard\Controller::attach
     * @covers \BjyAuthorize\Guard\Controller::detach
     */
    public function testAttachDetach()
    {
        $eventManager = $this->getMockBuilder('Laminas\\EventManager\\EventManagerInterface')
            ->getMock();

        $callbackMock = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $eventManager
            ->expects($this->once())
            ->method('attach')
            ->with()
            ->will($this->returnValue($callbackMock));

        $this->controllerGuard->attach($eventManager);
        $eventManager
            ->expects($this->once())
            ->method('detach')
            ->with($callbackMock)
            ->will($this->returnValue(true));
        $this->controllerGuard->detach($eventManager);
    }

    /**
     * @dataProvider controllersRulesProvider
     *
     * @covers \BjyAuthorize\Guard\Controller::__construct
     * @covers \BjyAuthorize\Guard\Controller::getResources
     * @covers \BjyAuthorize\Guard\Controller::getRules
     *
     * @param array     $rule
     * @param int       $expectedCount
     * @param string    $resource
     * @param array     $roles
     */
    public function testGetResourcesGetRules($rule, $expectedCount, $resource, $roles)
    {
        $controller = new Controller([$rule], $this->serviceLocator);

        $resources = $controller->getResources();

        $this->assertCount($expectedCount, $resources);
        $this->assertContains($resource, $resources);

        $rules = $controller->getRules();

        $this->assertCount($expectedCount, $rules['allow']);
        $this->assertContains([$roles, $resource], $rules['allow']);
    }

    /**
     * @dataProvider controllersRulesWithAssertionProvider
     *
     * @covers \BjyAuthorize\Guard\Controller::__construct
     * @covers \BjyAuthorize\Guard\Controller::getRules
     *
     * @param array     $rule
     * @param int       $expectedCount
     * @param string    $resource
     * @param array     $roles
     * @param string    $assertion
     */
    public function testGetRulesWithAssertion($rule, $expectedCount, $resource, $roles, $assertion)
    {
        $controller = new Controller([$rule], $this->serviceLocator);
        $rules      = $controller->getRules();

        $this->assertCount($expectedCount, $rules['allow']);
        $this->assertContains([$roles, $resource, null, $assertion], $rules['allow']);
    }

    /**
     * @covers \BjyAuthorize\Guard\Controller::getResourceName
     */
    public function testGetResourceName()
    {
        $this->assertSame('controller/test1:action1', $this->controllerGuard->getResourceName('test1', 'action1'));
        $this->assertSame('controller/test2', $this->controllerGuard->getResourceName('test2'));
        $this->assertSame('controller/test3:get', $this->controllerGuard->getResourceName('test3', 'GET'));
    }

    /**
     * @covers \BjyAuthorize\Guard\Controller::onDispatch
     */
    public function testOnDispatchWithValidController()
    {
        $event = $this->createMvcEvent('test-controller');
        $event->getTarget()->getEventManager()->expects($this->never())->method('trigger');
        $this
            ->authorize
            ->expects($this->any())
            ->method('isAllowed')
            ->will(
                $this->returnValue(
                    function ($resource) {
                        return $resource === 'controller/test-controller';
                    }
                )
            );

        $this->assertNull($this->controllerGuard->onDispatch($event), 'Does not stop event propagation');
    }

    /**
     * @covers \BjyAuthorize\Guard\Controller::onDispatch
     */
    public function testOnDispatchWithValidControllerAndAction()
    {
        $event = $this->createMvcEvent('test-controller', 'test-action');
        $event->getTarget()->getEventManager()->expects($this->never())->method('trigger');
        $this
            ->authorize
            ->expects($this->any())
            ->method('isAllowed')
            ->will(
                $this->returnValue(
                    function ($resource) {
                        return $resource === 'controller/test-controller:test-action';
                    }
                )
            );

        $this->assertNull($this->controllerGuard->onDispatch($event), 'Does not stop event propagation');
    }

    /**
     * @covers \BjyAuthorize\Guard\Controller::onDispatch
     */
    public function testOnDispatchWithValidControllerAndMethod()
    {
        $event = $this->createMvcEvent('test-controller', null, 'PUT');
        $event->getTarget()->getEventManager()->expects($this->never())->method('trigger');
        $this
            ->authorize
            ->expects($this->any())
            ->method('isAllowed')
            ->will(
                $this->returnValue(
                    function ($resource) {
                        return $resource === 'controller/test-controller:put';
                    }
                )
            );

        $this->assertNull($this->controllerGuard->onDispatch($event), 'Does not stop event propagation');
    }

    /**
     * @covers \BjyAuthorize\Guard\Controller::onDispatch
     */
    public function testOnDispatchWithValidControllerAction()
    {
        $event = $this->createMvcEvent('test-controller', 'test-action');
        $event->getTarget()->getEventManager()->expects($this->never())->method('trigger');
        $this
            ->authorize
            ->expects($this->any())
            ->method('isAllowed')
            ->with('controller/test-controller')
            ->will($this->returnValue(true));

        $this->assertNull($this->controllerGuard->onDispatch($event), 'Does not stop event propagation');
    }

    /**
     * @covers \BjyAuthorize\Guard\Controller::onDispatch
     */
    public function testOnDispatchWithInvalidResource()
    {
        $event = $this->createMvcEvent('test-controller', 'test-action');
        $this->authorize->expects($this->any())->method('getIdentity')->will($this->returnValue('admin'));
        $this
            ->authorize
            ->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue(false));
        $event->expects($this->once())->method('setError')->with(Controller::ERROR);

        $event->expects($this->at(5))->method('setParam')->with('identity', 'admin');
        $event->expects($this->at(6))->method('setParam')->with('controller', 'test-controller');
        $event->expects($this->at(7))->method('setParam')->with('action', 'test-action');
        $event->expects($this->at(8))->method('setParam')->with(
            'exception',
            $this->isInstanceOf(UnAuthorizedException::class)
        );

        $responseCollection = $this->getMockBuilder(\Laminas\EventManager\ResponseCollection::class)
            ->getMock();

        $event
            ->getTarget()
            ->getEventManager()
            ->expects($this->once())
            ->method('trigger')
            ->with(MvcEvent::EVENT_DISPATCH_ERROR, null, [])
            ->willReturn($responseCollection);

        $this->assertNull($this->controllerGuard->onDispatch($event), 'Does not stop event propagation');
    }

    /**
     * @covers \BjyAuthorize\Guard\Controller::onDispatch
     */
    public function testOnDispatchWithInvalidResourceConsole()
    {
        $event = $this->getMockBuilder('Laminas\\Mvc\\MvcEvent')
            ->setMethods(['getRequest', 'getRouteMatch'])
            ->getMock();
        $routeMatch   = $this->getMockBuilder('Laminas\\Mvc\\Router\\RouteMatch')
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->method('getRouteMatch')->willReturn($routeMatch);
        $event->method('getRequest')->willReturn($request);

        $this->assertNull($this->controllerGuard->onDispatch($event), 'Does not stop event propagation');
    }

    /**
     * @param string|null $controller
     * @param string|null $action
     * @param string|null $method
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Laminas\Mvc\MvcEvent
     */
    private function createMvcEvent($controller = null, $action = null, $method = null)
    {
        $eventManager = $this->getMockBuilder('Laminas\\EventManager\\EventManagerInterface')
            ->getMock();
        $application  = $this->getMockBuilder('Laminas\\Mvc\\Application')
            ->setMethods(['getEventManager'])
            ->disableOriginalConstructor()
            ->getMock();
        $event        = $this->getMockBuilder('Laminas\\Mvc\\MvcEvent')
            ->setMethods(['getTarget', 'getRouteMatch', 'getRequest', 'setError', 'setParam'])
            ->getMock();
        $routeMatch   = $this->getMockBuilder('Laminas\\Mvc\\Router\\RouteMatch')
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        $request      = $this->getMockBuilder('Laminas\\Http\\Request')
            ->getMock();

        $event->expects($this->any())->method('getRouteMatch')->will($this->returnValue($routeMatch));
        $event->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $event->expects($this->any())->method('getTarget')->will($this->returnValue($application));
        $application->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManager));
        $request->expects($this->any())->method('getMethod')->will($this->returnValue($method));
        $routeMatch
            ->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnCallback(
                    function ($param) use ($controller, $action) {
                        if ($param === 'controller') {
                            return $controller;
                        }

                        if ($param === 'action') {
                            return $action;
                        }

                        return null;
                    }
                )
            );

        return $event;
    }

    /**
     * Return a set of rules, with expected resources count, expected resource names
     * and expected output rules
     *
     * @return array
     */
    public function controllersRulesProvider()
    {
        return [
            [
                [
                    'controller' => 'test-controller',
                    'action'     => 'test-action',
                    'roles'      => [
                        'admin',
                        'user',
                    ],
                ],
                1,
                'controller/test-controller:test-action',
                ['admin', 'user']
            ],
            [
                [
                    'controller' => 'test2-controller',
                    'roles'      => [
                        'admin2',
                        'user2',
                    ],
                ],
                1,
                'controller/test2-controller',
                ['admin2', 'user2']
            ],
            [
                [
                    'controller' => 'test3-controller',
                    'action'     => 'test3-action',
                    'roles'      => 'admin3'
                ],
                1,
                'controller/test3-controller:test3-action',
                ['admin3']
            ],
            [
                [
                    'controller' => 'test4-controller',
                    'action'     => [
                        'test4-action',
                        'test5-action',
                    ],
                    'roles'      => [
                        'admin4',
                        'user3',
                    ],
                ],
                2,
                'controller/test4-controller:test4-action',
                ['admin4', 'user3']
            ],
            [
                [
                    'controller' => 'test4-controller',
                    'action'     => [
                        'test4-action',
                        'test5-action',
                    ],
                    'roles'      => [
                        'admin4',
                        'user3',
                    ],
                ],
                2,
                'controller/test4-controller:test5-action',
                ['admin4', 'user3']
            ],
            [
                [
                    'controller' => 'test5-controller',
                    'action'     => null,
                    'roles'      => 'user4'
                ],
                1,
                'controller/test5-controller',
                ['user4']
            ],
            [
                [
                    'controller' => [
                        'test6-controller',
                        'test7-controller',
                    ],
                    'action'     => null,
                    'roles'      => 'user5'
                ],
                2,
                'controller/test6-controller',
                ['user5']
            ],
            [
                [
                    'controller' => [
                        'test6-controller',
                        'test7-controller',
                    ],
                    'action'     => null,
                    'roles'      => 'user5'
                ],
                2,
                'controller/test7-controller',
                ['user5']
            ],
            [
                [
                    'controller' => [
                        'test6-controller',
                        'test7-controller',
                    ],
                    'action'     => [
                        'test6-action',
                        'test7-action',
                    ],
                    'roles'      => [
                        'admin5',
                        'user6',
                    ],
                ],
                4,
                'controller/test6-controller:test6-action',
                ['admin5', 'user6']
            ],
            [
                [
                    'controller' => [
                        'test6-controller',
                        'test7-controller',
                    ],
                    'action'     => [
                        'test6-action',
                        'test7-action',
                    ],
                    'roles'      => [
                        'admin5',
                        'user6',
                    ],
                ],
                4,
                'controller/test7-controller:test7-action',
                ['admin5', 'user6']
            ]
        ];
    }

    /**
     * Return a set of rules, with expected resources count, expected resource names,
     * expected output rules and expected assertion.
     *
     * @return array
     */
    public function controllersRulesWithAssertionProvider()
    {
        return [
            [
                [
                    'controller' => 'test-controller',
                    'action'     => 'test-action',
                    'roles'      => [
                        'admin',
                        'user',
                    ],
                    'assertion' => 'test-assertion'
                ],
                1,
                'controller/test-controller:test-action',
                ['admin', 'user'],
                'test-assertion'
            ],
            [
                [
                    'controller' => [
                        'test6-controller',
                        'test7-controller',
                    ],
                    'action'     => [
                        'test6-action',
                        'test7-action',
                    ],
                    'roles'      => [
                        'admin5',
                        'user6',
                    ],
                    'assertion' => 'test-assertion'
                ],
                4,
                'controller/test6-controller:test6-action',
                ['admin5', 'user6'],
                'test-assertion'
            ]
        ];
    }
}
