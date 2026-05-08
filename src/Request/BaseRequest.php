<?php

namespace App\Request;

abstract class BaseRequest
{
    /**
     * Define as regras de validação 
     */
    abstract public function rules(): array;

    /**
     * Define as mensagens personalizadas 
     */
    abstract public function messages(): array;

    /**
     * Recebe os dados ($_POST) e para onde voltar se der erro.
     */
    public function validate(array $data, string $redirectUrl): array
    {
        // Chama a função helper global 'validator'
        // Usa as regras e mensagens definidas na classe filha
        $validador = validator($data, $this->rules(), $this->messages());

        // Se falhar redirecionar
        if ($validador->fails()) {

            session_flash('errors', $validador->errors()->all());
            
            // Salva os dados antigos (exceto senha)
            if (isset($data['senha'])) {
                unset($data['senha']);
            }
            session_flash('old', $data);

            header('Location: ' . $redirectUrl);
            exit;
        }

        // Se deu  certo retorna os dados validados!
        return $data;
    }
}