<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Unique_field implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return count($value) === count(array_intersect_key( $value , array_unique( array_map('serialize' , $value ) ) ));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Existem linhas iguais na tabela. Por favor certifique-se que todas as linhas são diferentes!');
    }
}
