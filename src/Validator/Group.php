<?php

namespace Spartan\Validation\Validator;

use Laminas\Validator\CreditCard;
use Laminas\Validator\Digits;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\Hostname;
use Laminas\Validator\InArray;
use Laminas\Validator\LessThan;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorInterface;

/**
 * Payload Validator
 *
 * @package Spartan\Validator
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Group implements ValidatorInterface
{
    const STOP_ON_FIRST = 1;
    const STOP_ON_EACH  = 2;

    /**
     * @var mixed[]
     */
    protected array $rules = [];

    /**
     * @var mixed[]
     */
    protected array $messages = [];

    /**
     * @var mixed[]
     */
    protected array $aliases = [
        'latin'    => [Regex::class, '/^[a-zA-Z]+$/'],
        'latinum'  => [Regex::class, '/^[a-zA-Z0-9]+$/'],
        'alpha'    => [Regex::class, '/^[\p{L}\p{N}]+$/'],
        'alphanum' => [Regex::class, '/^[\p{L}\p{N}0-9]+$/'],
        'card'     => CreditCard::class,
        'num'      => Digits::class,
        'numeric'  => Digits::class,
        'email'    => EmailAddress::class,
        'gt'       => GreaterThan::class,
        'gte'      => [GreaterThan::class, null, true],
        'lt'       => LessThan::class,
        'lte'      => [LessThan::class, null, true],
        'host'     => Hostname::class,
        'in'       => InArray::class,
        'enum'     => InArray::class,
        'length'   => StringLength::class,
    ];

    protected int $options = 0;

    /**
     * Group constructor.
     *
     * @param mixed $options
     */
    public function __construct($options)
    {
        $this->rules   = $options['rules'] ?? $options;
        $this->options = $options['mode'] ?? self::STOP_ON_FIRST;
        $this->aliases = ($options['aliases'] ?? []) + $this->aliases;
    }

    /**
     * @inheritDoc
     */
    public function isValid($values): bool
    {
        foreach ($this->rules as $key => $rules) {
            $value = $values[$key] ?? null;

            foreach ((array)$rules as $rule) {
                $isAlias = false;
                $rule    = (array)$rule;
                if (is_string($rule[0]) && isset($this->aliases[$rule[0]])) {
                    $rule    = (array)$this->aliases[$rule[0]] + $rule;
                    $isAlias = true;
                }

                $params    = (array)$rule;
                $validator = array_shift($params);

                if (is_string($validator)) {
                    $laminasValidator = "\\Laminas\\Validator\\{$validator}";
                    $spartanValidator = "\\Spartan\\Validator\\{$validator}";

                    if ($isAlias) {
                        $validator = new $validator(...$params);
                    } elseif (class_exists($laminasValidator)) {
                        $validator = new $laminasValidator(...$params);
                    } elseif (class_exists($spartanValidator)) {
                        $validator = new $spartanValidator(...$params);
                    } else {
                        throw new \InvalidArgumentException('Could not locate validator ' . json_encode($validator));
                    }
                }

                if (!$validator->isValid($value)) {
                    $this->messages += $validator->getMessages();

                    if ($this->options & self::STOP_ON_FIRST) {
                        return false;
                    }

                    if ($this->options & self::STOP_ON_EACH) {
                        continue 2;
                    }
                }
            }
        }

        return $this->messages === [];
    }

    /**
     * @return mixed[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
