<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Tests\Constraints;

use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints\Slug;
use Symfony\Component\Validator\Constraints\SlugValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class SlugValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): SlugValidator
    {
        return new SlugValidator();
    }

    public function testNullIsValid()
    {
        $this->validator->validate(null, new Slug());

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new Slug());

        $this->assertNoViolation();
    }

    public function testExpectsStringCompatibleType()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(new \stdClass(), new Slug());
    }

    /**
     * @testWith ["test-slug"]
     *           ["slug-123-test"]
     *           ["slug"]
     *           ["TestSlug"]
     */
    public function testValidSlugs($slug)
    {
        $this->validator->validate($slug, new Slug());

        $this->assertNoViolation();
    }

    /**
     * @testWith ["Not a slug"]
     *           ["not-á-slug"]
     *           ["not-@-slug"]
     */
    public function testInvalidSlugs($slug)
    {
        $constraint = new Slug(message: 'myMessage');

        $this->validator->validate($slug, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ value }}', '"'.$slug.'"')
            ->setCode(Slug::NOT_SLUG_ERROR)
            ->assertRaised();
    }

    /**
     * @testWith ["test-slug", true]
     *           ["slug-123-test", true]
     */
    public function testCustomRegexInvalidSlugs($slug)
    {
        $constraint = new Slug(regex: '/^[a-z0-9]+$/i');

        $this->validator->validate($slug, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', '"'.$slug.'"')
            ->setCode(Slug::NOT_SLUG_ERROR)
            ->assertRaised();
    }

    /**
     * @testWith ["slug"]
     *           ["test1234"]
     */
    public function testCustomRegexValidSlugs($slug)
    {
        $constraint = new Slug(regex: '/^[a-z0-9]+$/i');

        $this->validator->validate($slug, $constraint);

        $this->assertNoViolation();
    }

    /**
     * @testWith ["PHP"]
     *           ["Symfony is cool"]
     *           ["Lorem ipsum dolor sit amet"]
     */
    public function testAcceptAsciiSluggerResults(string $text)
    {
        $this->validator->validate((new AsciiSlugger())->slug($text), new Slug());

        $this->assertNoViolation();
    }
}
