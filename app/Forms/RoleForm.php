<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;
use Kris\LaravelFormBuilder\Field;

class RoleForm extends Form
{
    public function buildForm()
    {
        $this->add('name', 'text', [
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
        ])->add('display_name', 'text', [
			'wrapper' => ['class' => 'form-group'],
			'attr' => ['class' => 'form-control'],
			'help_block' => [
			    'text' => null,
			    'tag' => 'p',
			    'attr' => ['class' => 'help-block']
			],
			'default_value' => !empty($this->model->display_name) ? $this->model->display_name : null, // Fallback value if none provided by value property or model
			'label' => __('Nome a Mostrar'),  // Field name used
			'label_show' => true,
			'label_attr' => ['class' => 'control-label', 'for' => $this->name],
			'errors' => ['class' => 'text-danger'],
			'rules' => 'required',           // Validation rules
			'error_messages' => [
				'display_name.required' => __('Nome a Mostrar Requerido!')
			]   // Validation error messages
        ])->add('description', 'textarea', [
			'wrapper' => ['class' => 'form-group'],
			'attr' => ['class' => 'form-control'],
			'help_block' => [
			    'text' => null,
			    'tag' => 'p',
			    'attr' => ['class' => 'help-block']
			],
			'default_value' => !empty($this->model->description) ? $this->model->description : null, // Fallback value if none provided by value property or model
			'label' => __('Descrição'),  // Field name used
			'label_show' => true,
			'label_attr' => ['class' => 'control-label', 'for' => $this->name],
			'errors' => ['class' => 'text-danger'],
			'rules' => [],           // Validation rules
			'error_messages' => []   // Validation error messages
        ])->add('permissions', 'entity', [
        	'wrapper' => ['class' => 'form-group'],
			'attr' => ['class' => 'form-control selectpicker' , 'title'=> __('Selecione Permissões...') , 'data-live-search' => 'true' , 'multiple data-actions-box'=>"true"],
			'help_block' => [
			    'text' => null,
			    'tag' => 'p',
			    'attr' => ['class' => 'help-block']
			],
			'label' => __('Permissões'),
	        'class' => 'App\Models\Permission',
	        'property' => 'display_name',
	        'multiple' => true,
	        'selected' => !empty($this->model->permissions) ? $this->model->permissions : null,
    	])->add('create_role', 'submit', [
        	'wrapper' => ['class' => 'form-group text-right'],
    		'attr' => ['class' => 'btn btn-primary'],
    		'label' => $this->formOptions['method'] == 'POST' ? __('Criar Cargo') : __('Editar Cargo')
        ]);
    }
}
