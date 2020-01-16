<?php
/**
 * Jield BV all rights reserved
 *
 * PHP Version 7
 *
 * @category    Mailing
 *
 * @author      Dr. ir. Johan van der Heide <info@jield.nl>
 * @copyright   Copyright (c) 2004-2017 Jield BV (https://jield.nl) (http://jield.nl)
 * @license     http://jield.nl/license.txt proprietary
 *
 *
 */
declare(strict_types=1);

namespace BjyAuthorize\Factory;

use BjyAuthorize\Service\AddAssets;
use BjyAuthorize\Service\Authorize;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class AddAssetsFactory
 * @package BjyAuthorize\Factory
 */
final class AddAssetsFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AddAssets
    {
        return new AddAssets(
            $container,
            $container->get(Authorize::class),
            $container->get('BjyAuthorize\Guards'),
            $container->get('BjyAuthorize\RuleProviders')
        );
    }
}
