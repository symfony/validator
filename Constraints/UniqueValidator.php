<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @author Yevgeniy Zholkevskiy <zhenya.zholkevskiy@gmail.com>
 */
class UniqueValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Unique) {
            throw new UnexpectedTypeException($constraint, Unique::class);
        }

        $fields = (array) $constraint->fields;

        if (null === $value) {
            return;
        }

        if (!\is_array($value) && !$value instanceof \IteratorAggregate) {
            throw new UnexpectedValueException($value, 'array|IteratorAggregate');
        }

        $collectionElements = [];
        $normalizer = $this->getNormalizer($constraint);
        foreach ($value as $element) {
            $element = $normalizer($element);

            if ($fields && !$element = $this->reduceElementKeys($fields, $element)) {
                continue;
            }

            if (\in_array($element, $collectionElements, true)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $this->formatValue($value))
                    ->setCode(Unique::IS_NOT_UNIQUE)
                    ->addViolation();

                return;
            }
            $collectionElements[] = $element;
        }
    }

    private function getNormalizer(Unique $unique): callable
    {
        if (null === $unique->normalizer) {
            return static fn ($value) => $value;
        }

        return $unique->normalizer;
    }

    private function reduceElementKeys(array $fields, array|object $element): array
    {
        $output = [];
        foreach ($fields as $field) {
            if (!\is_string($field)) {
                throw new UnexpectedTypeException($field, 'string');
            }

            if (is_array($element)) {
                if (\array_key_exists($field, $element)) {
                    $output[$field] = $element[$field];
                }
            } else if (is_object($element)) {
                $accessor = PropertyAccess
                    ::createPropertyAccessor();
                if ($accessor->isReadable($element, $field)) {
                    $output[$field] = $accessor->getValue($element, $field);
                }
            }
        }

        return $output;
    }
}
