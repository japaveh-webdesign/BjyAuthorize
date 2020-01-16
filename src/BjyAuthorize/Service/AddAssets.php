<?php
/**
 * Jield BV all rights reserved
 *
 * @category  Application
 *
 * @author    Dr. ir. Johan van der Heide <info@jield.nl>
 * @copyright Copyright (c) 2004-2017 Jield BV (https://jield.nl)
 */

declare(strict_types=1);

namespace BjyAuthorize\Service;

use Interop\Container\ContainerInterface;
use Laminas\Permissions\Acl\Acl;

use function count;
use function is_string;

/**
 * Class AddAssets
 *
 * @package Application\Service
 */
final class AddAssets
{
    protected ContainerInterface $container;
    protected Acl $acl;
    protected array $guards;
    protected array $ruleProviders;

    public function __construct(
        ContainerInterface $container,
        Authorize $authorize,
        array $guards,
        array $ruleProviders
    )
    {
        $this->container     = $container;
        $this->acl           = $authorize->getAcl();
        $this->guards        = $guards;
        $this->ruleProviders = $ruleProviders;
    }

    public function __invoke(): self
    {
        //Grab the Acl from the event list
        foreach ($this->guards as $guard) {
            $rules = $guard->getRules();
            if (isset($rules['allow'])) {
                foreach ($rules['allow'] as $rule) {
                    $this->loadRule($rule, Authorize::TYPE_ALLOW);
                }
            }
            if (isset($rules['deny'])) {
                foreach ($rules['deny'] as $rule) {
                    $this->loadRule($rule, Authorize::TYPE_DENY);
                }
            }
        };
        foreach ($this->ruleProviders as $provider) {
            $rules = $provider->getRules();
            if (isset($rules['allow'])) {
                foreach ($rules['allow'] as $rule) {
                    $this->loadRule($rule, Authorize::TYPE_ALLOW);
                }
            }
            if (isset($rules['deny'])) {
                foreach ($rules['deny'] as $rule) {
                    $this->loadRule($rule, Authorize::TYPE_DENY);
                }
            }
        }

        return $this;
    }

    protected function loadRule(array $rule, $type): bool
    {
        $privileges = null;
        $assertion  = null;
        $ruleSize   = count($rule);
        if ($ruleSize < 4) {
            return true;
        }
        [$roles, $resources, $privileges, $assertion] = $rule;

        if (is_string($assertion)) {
            $assertion = $this->container->get($assertion);
        }

        if (Authorize::TYPE_ALLOW === $type) {
            $this->acl->allow($roles, $resources, $privileges, $assertion);
        } else {
            $this->acl->deny($roles, $resources, $privileges, $assertion);
        }

        return true;
    }
}
