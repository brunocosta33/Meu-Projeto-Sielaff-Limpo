<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;
use Kris\LaravelFormBuilder\Field;

use App\Rules\Old_Password;

class ChangePasswordForm extends Form
{
    public function buildForm()
    {
        $this->add('old_password', 'password', [
			'wrapper' => ['class' => 'form-group'],
			'attr' => ['class' => 'form-control'],
			'help_block' => [
			    'text' => null,
			    'tag' => 'p',
			    'attr' => ['class' => 'help-block']
			],
			'default_value' => null, // Fallback value if none provided by value property or model
			'label' => __('Antiga Password'),  // Field name used
			'label_show' => true,
			'label_attr' => ['class' => 'control-label', 'for' => $this->name],
			'errors' => ['class' => 'text-danger'],
			'rules' => ['required', new Old_Password],           // Validation rules
			'error_messages' => [
				'old_password.required' => __('Password Antiga requerida!'),
				'old_password.old_password' => __('Password não coincide com a antiga!'),
			]   // Validation error messages
        ])->add('new_password', 'password', [
			'wrapper' => ['class' => 'form-group'],
			'attr' => ['class' => 'form-control'],
			'help_block' => [
			    'text' => null,
			    'tag' => 'p',
			    'attr' => ['class' => 'help-block']
			],
			'default_value' => null, // Fallback value if none provided by value property or model
			'label' => __('Nova Password'),  // Field name used
			'label_show' => true,
			'label_attr' => ['class' => 'control-label', 'for' => $this->name],
			'errors' => ['class' => 'text-danger'],
			'rules' => 'required | confirmed',           // Validation rules
			'error_messages' => [
				'new_password.required' => __('Password requerida!'),
				'new_password.confirmed' => __('Password não coincide!'),
			]   // Validation error messages
        ])->add('new_password_confirmation', 'password', [
			'wrapper' => ['class' => 'form-group'],
			'attr' => ['class' => 'form-control'],
			'help_block' => [
			    'text' => null,
			    'tag' => 'p',
			    'attr' => ['class' => 'help-block']
			],
			'default_value' => null, // Fallback value if none provided by value property or model
			'label' => __('Confirmar Nova Password'),  // Field name used
			'label_show' => true,
			'label_attr' => ['class' => 'control-label', 'for' => $this->name],
			'errors' => ['class' => 'text-danger'],
			'rules' => '',           // Validation rules
			'error_messages' => []   // Validation error messages
        ])->add('change_password', 'submit', [
        	'wrapper' => ['class' => 'form-group text-right'],
    		'attr' => ['class' => 'btn btn-primary'],
    		'label' => __('Alterar Password')
        ]);
    }
}
