<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Validator\DependencyInjection\AttributeMetadataPass;

class AttributeMetadataPassTest extends TestCase
{
    public function testProcessWithNoValidatorBuilder()
    {
        $container = new ContainerBuilder();

        // Should not throw any exception
        (new AttributeMetadataPass())->process($container);

        $this->expectNotToPerformAssertions();
    }

    public function testProcessWithValidatorBuilderButNoTaggedServices()
    {
        $container = new ContainerBuilder();
        $container->register('validator.builder');

        $pass = new AttributeMetadataPass();
        $pass->process($container);

        $methodCalls = $container->getDefinition('validator.builder')->getMethodCalls();
        $this->assertCount(0, $methodCalls);
    }

    public function testProcessWithTaggedServices()
    {
        $container = new ContainerBuilder();
        $container->setParameter('user_entity.class', 'App\Entity\User');
        $container->register('validator.builder')
            ->addMethodCall('addAttributeMappings', [[]]);

        $container->register('service1', '%user_entity.class%')
            ->addTag('validator.attribute_metadata')
            ->addTag('container.excluded');
        $container->register('service2', 'App\Entity\Product')
            ->addTag('validator.attribute_metadata')
            ->addTag('container.excluded');
        $container->register('service3', 'App\Entity\Order')
            ->addTag('validator.attribute_metadata')
            ->addTag('container.excluded');
        // Classes should be deduplicated
        $container->register('service4', 'App\Entity\Order')
            ->addTag('validator.attribute_metadata')
            ->addTag('container.excluded');

        (new AttributeMetadataPass())->process($container);

        $methodCalls = $container->getDefinition('validator.builder')->getMethodCalls();
        $this->assertCount(2, $methodCalls);
        $this->assertEquals('addAttributeMappings', $methodCalls[1][0]);

        // Classes should be sorted alphabetically
        $expectedClasses = ['App\Entity\Order', 'App\Entity\Product', 'App\Entity\User'];
        $this->assertEquals([$expectedClasses], $methodCalls[1][1]);
    }
}
