<?php

namespace App\Request;

class UsuarioStoreRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'nome' => 'required|min:3',
            'email' => 'required|email|unique:usuarios,email',
            'senha' => 'required|min:6',
            'situacao_id' => 'required',
            'nivel' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo Nome é obrigatório.',
            'nome.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'email.required' => 'O campo E-mail é obrigatório.',
            'email.email' => 'Email inválido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'situacao_id.required'=> 'O Campo Situação é obrigatório.',
            'senha.required' => 'O campo Senha é obrigatório.',
            'senha.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'nivel.required' => 'O nível é obrigatório.'
        ];
    }
}