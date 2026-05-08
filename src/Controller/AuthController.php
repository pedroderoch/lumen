<?php

namespace App\Controller;

use App\Model\Usuario;
use App\Request\BaseRequest;

class AuthController extends BaseController
{
    /**
     * Exibe a tela de login (Sempre a porta de entrada)
     */
    public function index(): void
    {
        // Se o usuário já estiver logado, não precisa ver o login, manda para o dash
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }

        $this->render('auth/login.html.twig');
    }

    /**
     * Processa a autenticação
     */
    public function login(): void
    {
        // 1. Coleta e limpa os inputs
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        // Validação básica de preenchimento
        if (empty($email) || empty($senha)) {
            session_flash('errors', ['Por favor, preencha todos os campos.']);
            header('Location: /login');
            exit;
        }

        // 2. Busca o usuário ativo (não excluído logicamente)
        // Onde situacao_id != 3 (conforme nossa documentação de exclusão lógica)
        $usuario = Usuario::where('email', $email)
                          ->where('situacao_id', '!=', 3)
                          ->first();

        // 3. Verificação de Credenciais
        // O password_verify compara o texto puro com o hash BCRYPT do banco
        if ($usuario && password_verify($senha, $usuario->senha)) {
            
            // Login bem-sucedido: Registra os dados essenciais na Sessão
            $_SESSION['user_id']    = $usuario->id;
            $_SESSION['user_nome']  = $usuario->nome;
            $_SESSION['user_nivel'] = $usuario->nivel;

            session_flash('success', "Bem-vindo ao sistema, {$usuario->nome}!");
            
            // Redireciona para o Dashboard (raiz do localhost)
            header('Location: /'); 
            exit;
        }

        // 4. Falha na Autenticação
        // Usamos mensagens genéricas por segurança (não dizer se o erro foi o email ou a senha)
        session_flash('errors', ['Credenciais inválidas ou conta desativada.']);
        session_flash('old', ['email' => $email]); // Mantém o email no campo para facilitar
        
        header('Location: /login');
        exit;
    }

    /**
     * Encerra a conexão e limpa a sessão
     */
    public function logout(): void
    {
        // Remove as variáveis específicas
        unset($_SESSION['user_id'], $_SESSION['user_nome'], $_SESSION['user_nivel']);
        
        // Destrói a sessão completamente
        session_destroy();
        
        // Reinicia para poder enviar a mensagem de sucesso do logout
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        session_flash('success', 'Sessão encerrada com segurança.');
        header('Location: /login');
        exit;
    }
}