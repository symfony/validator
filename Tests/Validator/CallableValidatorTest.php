<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Tests\Validator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Validator\CallableValidator;

/**
 * @author Jan Vernieuwe <jan.vernieuwe@phpro.be>
 */
class CallableValidatorTest extends TestCase
{
    public function testValidate()
    {
        $validator = new CallableValidator([new Length(['min' => 10]), new Email()]);
        $this->assertEquals('test@example.com', $validator('test@example.com'));
        $this->expectException(LogicException::class, 'Symfony\Component\Validator\Exception\LogicException : test:
    This value is too short. It should have 10 characters or more. (code 9ff3fdc4-b214-49db-8718-39c315e33d45)
test:
    This value is not a valid email address. (code bd79c0ab-ddba-46cc-a703-a7a4b08de310)');
        $validator('test');
    }
}
