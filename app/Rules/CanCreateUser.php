<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\Models\User;

class CanCreateUser implements Rule
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
        $user = User::whereEmail($value)->first();
        return empty($user) || !empty($user->deleted_at);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('There is already an active user with this email!');
    }
}
