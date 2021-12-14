<?php

namespace Spartan\Validation\Validator;

use Laminas\Validator\ValidatorInterface;

/**
 * NonDisposableEmail Validator
 *
 * @see https://disposable-emails.github.io/
 *
 * @package Spartan\Validator
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class NonDisposableEmail implements ValidatorInterface
{
    protected $messages = [];

    /**
     * @inheritDoc
     */
    public function isValid($value): bool
    {
        $pos    = (int)strrpos($value, '@');
        $domain = substr($value, $pos ? $pos + 1 : 0);

        $domains = array_flip(explode("\n", file_get_contents(__DIR__ . '/../../data/disposable.txt')));

        if (isset($domains[$domain])) {
            $this->messages['email_disposable'] = 'Email domain is disposable.';
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
