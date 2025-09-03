<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
final class AttributeMetadataPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('validator.builder')) {
            return;
        }

        $resolve = $container->getParameterBag()->resolveValue(...);
        $mappedClasses = [];
        foreach ($container->getDefinitions() as $id => $definition) {
            if (!$definition->hasTag('validator.attribute_metadata')) {
                continue;
            }
            if (!$definition->hasTag('container.excluded')) {
                throw new InvalidArgumentException(\sprintf('The resource "%s" tagged "validator.attribute_metadata" is missing the "container.excluded" tag.', $id));
            }
            $mappedClasses[$resolve($definition->getClass())] = true;
        }

        if (!$mappedClasses) {
            return;
        }

        ksort($mappedClasses);

        $container->getDefinition('validator.builder')
            ->addMethodCall('addAttributeMappings', [array_keys($mappedClasses)]);
    }
}
