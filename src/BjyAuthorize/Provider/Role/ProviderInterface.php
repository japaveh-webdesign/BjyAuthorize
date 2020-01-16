<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorize\Provider\Role;

use Laminas\Permissions\Acl\Role\RoleInterface;

/**
 * Role provider interface, provides existing roles list
 *
 * @author Ben Youngblood <bx.youngblood@gmail.com>
 */
interface ProviderInterface
{
    /**
     * @return RoleInterface[]
     */
    public function getRoles();
}
