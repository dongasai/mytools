<?php

namespace DLaravel\Validator;


/**
 *
 */
class EmptyValidator extends Validator
{
    public function validate(mixed $value, array $data): bool
    {
        return true;
    }

}
