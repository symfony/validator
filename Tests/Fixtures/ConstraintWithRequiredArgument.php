<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Tests\Fixtures;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class ConstraintWithRequiredArgument extends Constraint
{
    public string $requiredArg;

    #[HasNamedArguments]
    public function __construct(string $requiredArg, ?array $groups = null, mixed $payload = null)
    {
        parent::__construct([], $groups, $payload);

        $this->requiredArg = $requiredArg;
    }
}
