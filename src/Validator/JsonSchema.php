<?php

namespace Spartan\Validation\Validator;

use Laminas\Validator\ValidatorInterface;

/**
 * JsonSchema Validator
 *
 * @package Spartan\Validator
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class JsonSchema implements ValidatorInterface
{
    /**
     * @var mixed[]
     */
    protected array $schema;

    /**
     * @var mixed[]
     */
    protected array $messages;

    /**
     * JsonSchema constructor.
     *
     * @param mixed[] $schema
     */
    public function __construct(array $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @param mixed $values
     *
     * @return bool
     * @throws \Swaggest\JsonSchema\Exception
     * @throws \Swaggest\JsonSchema\InvalidValue
     */
    public function isValid($values): bool
    {
        // check JSON schema
        $context = new \Swaggest\JsonSchema\Context();
        $schema  = \Swaggest\JsonSchema\Schema::import(
            json_decode((string)json_encode($this->schema)),
            $context
        );

        try {
            $schema->in(json_decode((string)json_encode($values)));
        } catch (\Exception $e) {
            $this->messages = [
                $e->getCode() => $e->getMessage(),
            ];

            return false;
        }

        return true;
    }

    /**
     * @return mixed[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
