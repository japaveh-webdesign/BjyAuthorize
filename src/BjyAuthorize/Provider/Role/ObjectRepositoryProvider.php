<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorize\Provider\Role;

use BjyAuthorize\Acl\HierarchicalRoleInterface;
use BjyAuthorize\Acl\Role;
use Doctrine\Common\Persistence\ObjectRepository;
use Laminas\Permissions\Acl\Role\RoleInterface;

/**
 * Role provider based on a {@see \Doctrine\Common\Persistence\ObjectRepository}
 *
 * @author Tom Oram <tom@scl.co.uk>
 */
class ObjectRepositoryProvider implements ProviderInterface
{
    /**
     * @var ObjectRepository
     */
    protected $objectRepository;

    /**
     * @param ObjectRepository $objectRepository
     */
    public function __construct(ObjectRepository $objectRepository)
    {
        $this->objectRepository = $objectRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        $result = $this->objectRepository->findAll();
        $roles = [];

        // Pass One: Build each object
        foreach ($result as $role) {
            if (!$role instanceof RoleInterface) {
                continue;
            }

            $roleId = $role->getRoleId();
            $parent = null;

            if ($role instanceof HierarchicalRoleInterface && $parent = $role->getParent()) {
                $parent = $parent->getRoleId();
            }

            $roles[$roleId] = new Role($roleId, $parent);
        }

        // Pass Two: Re-inject parent objects to preserve hierarchy
        /* @var $roleObj Role */
        foreach ($roles as $roleObj) {
            $parentRoleObj = $roleObj->getParent();

            if ($parentRoleObj && $parentRoleObj->getRoleId()) {
                $roleObj->setParent($roles[$parentRoleObj->getRoleId()]);
            }
        }

        return array_values($roles);
    }
}
