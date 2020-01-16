<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorize\Provider\Identity;

use BjyAuthorize\Exception\InvalidRoleException;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Permissions\Acl\Role\RoleInterface;
use ZfcUser\Service\User;

/**
 * Identity provider based on {@see \Laminas\Db\Adapter\Adapter}
 *
 * @author Ben Youngblood <bx.youngblood@gmail.com>
 */
class ZfcUserZendDb implements ProviderInterface
{
    /**
     * @var User
     */
    protected $userService;

    /**
     * @var string|RoleInterface
     */
    protected $defaultRole;

    /**
     * @var string
     */
    protected $tableName = 'user_role_linker';

    /**
     * @var TableGateway
     */
    private $tableGateway;

    /**
     * @param TableGateway $tableGateway
     * @param User $userService
     */
    public function __construct(TableGateway $tableGateway, User $userService)
    {
        $this->tableGateway = $tableGateway;
        $this->userService = $userService;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentityRoles()
    {
        $authService = $this->userService->getAuthService();

        if (!$authService->hasIdentity()) {
            return [$this->getDefaultRole()];
        }

        // get roles associated with the logged in user
        $sql = new Select();

        $sql->from($this->tableName);
        // @todo these fields should eventually be configurable
        $sql->join('user_role', 'user_role.id = ' . $this->tableName . '.role_id');
        $sql->where(['user_id' => $authService->getIdentity()->getId()]);

        $results = $this->tableGateway->selectWith($sql);

        $roles = [];

        foreach ($results as $role) {
            $roles[] = $role['role_id'];
        }

        return $roles;
    }

    /**
     * @return string|RoleInterface
     */
    public function getDefaultRole()
    {
        return $this->defaultRole;
    }

    /**
     * @param string|RoleInterface $defaultRole
     *
     * @throws InvalidRoleException
     */
    public function setDefaultRole($defaultRole)
    {
        if (!($defaultRole instanceof RoleInterface || is_string($defaultRole))) {
            throw InvalidRoleException::invalidRoleInstance($defaultRole);
        }

        $this->defaultRole = $defaultRole;
    }
}
