<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

use App\Rules\CanCreateUser;

class UserForm extends Form
{
    public function buildForm()
    {
        $this->add('is_active', 'checkbox', [
        	'wrapper' =>['class' => 'form-group form-check custom-control custom-checkbox'],
        	'attr' => ['class' => 'form-check-input custom-control-input'],
			'label' => __('is_active'),  // Field name used
			'label_show' => true,
			'label_attr' => ['class' => 'form-check-label custom-control-label', 'for' => $this->is_active],
		    'value' => 1,
		    'default_value' => !empty($this->model->name) ? $this->model->is_active ? true : false : true,
		])->add('name', 'text', [
			'wrapper' => ['class' => 'form-group'],
			'attr' => ['class' => 'form-control'],
			'help_block' => [
			    'text' => null,
			    'tag' => 'p',
			    'attr' => ['class' => 'help-block']
			],
			'default_value' => !empty($this->model->name) ? $this->model->name : null, // Fallback value if none provided by value property or model
			'label' => __('Nome'),  // Field name used
			'label_show' => true,
			'label_attr' => ['class' => 'control-label', 'for' => $this->name],
			'errors' => ['class' => 'text-danger'],
			'rules' => 'required',           // Validation rules
			'error_messages' => [
				'name.required' => __('Nome requerido!')
			]   // Validation error messages
        ])->add('email', 'email', [
			'wrapper' => ['class' => 'form-group'],
			'attr' => ['class' => 'form-control'],
			'help_block' => [
			    'text' => null,
			    'tag' => 'p',
			    'attr' => ['class' => 'help-block']
			],
			'default_value' => !empty($this->model->email) ? $this->model->email : null, // Fallback value if none provided by value property or model
			'label' => __('Email'),  // Field name used
			'label_show' => true,
			'label_attr' => ['class' => 'control-label', 'for' => $this->name],
			'errors' => ['class' => 'text-danger'],
			'rules' => 'required | email',           // Validation rules
			'error_messages' => [
				'email.required' => __('Nome a Mostrar Requerido!'),
				'email.email' => __('Email invalido!')
			]   // Validation error messages
        ])->add('role', 'entity', [
        	'wrapper' => ['class' => 'form-group'],
			'attr' => ['class' => 'form-control selectpicker' , 'title'=> __('Selecione um Cargo...') , 'data-live-search' => 'true' , 'multiple data-actions-box'=>"true"],
			'help_block' => [
			    'text' => null,
			    'tag' => 'p',
			    'attr' => ['class' => 'help-block']
			],
			'label' => __('Cargo'),
	        'class' => 'App\Role',
	        'property' => 'display_name',
	        'multiple' => true,
	        'selected' => !empty($this->model->roles) ? $this->model->roles : null,
	        'empty_value' => __('Selecione um Cargo...')
    	])->add('permission', 'entity', [
        	'wrapper' => ['class' => 'form-group'],
			'attr' => ['class' => 'form-control selectpicker' , 'title'=> __('Selecione Permissões...')  , 'data-live-search' => 'true' , 'multiple data-actions-box'=>"true"],
			'help_block' => [
			    'text' => null,
			    'tag' => 'p',
			    'attr' => ['class' => 'help-block']
			],
			'label' => __('Permissões'),
	        'class' => 'App\Permission',
	        'property' => 'display_name',
	        'multiple' => true,
	        'selected' => !empty($this->model->permissions) ? $this->model->permissions : null,
	        'empty_value' => __('Selecione Permissões...')
    	])->add('create_user', 'submit', [
        	'wrapper' => ['class' => 'form-group text-right'],
    		'attr' => ['class' => 'btn btn-primary'],
    		'label' => $this->formOptions['method'] == 'POST' ? __('Criar Utilizador') : __('Editar Utilizador')
        ]);
    }
}
