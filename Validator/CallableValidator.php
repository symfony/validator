<?php

namespace Symfony\Component\Validator\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Exception\LogicException;

/**
 * Class that enables usage of the validators in console questions
 *
 * @author Jan Vernieuwe <jan.vernieuwe@phpro.be>
 */
class CallableValidator
{
    /**
     * @var Constraint[]
     */
    private $constraints;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * CallableValidator constructor.
     *
     * @param Constraint[] $constraints
     */
    public function __construct(array $constraints)
    {
        $this->constraints = $constraints;
        $this->validator = Validation::createValidator();
    }

    /**
     * @param string $value
     *
     * @return string|null
     * @throws LogicException
     */
    public function __invoke(string $value = null): string
    {
        $violations = $this->validator->validate($value, $this->constraints);
        if (0 !== $violations->count()) {
            throw new LogicException((string)$violations);
        }

        return $value;
    }
}
