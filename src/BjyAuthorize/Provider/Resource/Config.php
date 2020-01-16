<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyAuthorize\Provider\Resource;

use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * Array-based resources list
 *
 * @author Ben Youngblood <bx.youngblood@gmail.com>
 */
class Config implements ProviderInterface
{
    /**
     * @var ResourceInterface[]
     */
    protected $resources = [];

    /**
     * @param ResourceInterface[] $config
     */
    public function __construct(array $config = [])
    {
        $this->resources = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getResources()
    {
        return $this->resources;
    }
}
