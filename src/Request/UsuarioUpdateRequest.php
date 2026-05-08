<?php

namespace App\Request;

class UsuarioUpdateRequest extends BaseRequest
{
    private int $id;

    // CONSTRUTOR PARA RECEBER O ID
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function rules(): array
    {
        return [
            'nome'    => 'required|min:3',
            // REGRA UNIQUE COM EXCEÇÃO
            // Concatenamos o ID no final para o Validador ignorar este usuário
            'email'   => 'required|email|unique:usuarios,email,' . $this->id,
            // SENHA OPCIONAL
            // Se vier a senha no formulário, tem que ter min 6. Se não vier (vazio), passa (nullable).
            'senha'   => 'nullable|min:6',
            'situacao_id' => 'required',
            'nivel'   => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'    => 'O campo Nome é obrigatório.',
            'email.required'   => 'O campo E-mail é obrigatório.',
            'situacao_id.required'=> 'O Campo Situação é obrigatório.',
            'email.unique'     => 'Este e-mail já está em uso.',
            'senha.min'        => 'A senha deve ter pelo menos 6 caracteres.',
            'nivel.required'   => 'O nível é obrigatório.'
        ];
    }
}