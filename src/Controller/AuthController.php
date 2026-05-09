<?php

namespace App\Controller;

use App\Model\Usuario;
use App\Model\Situacao;
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

    public function primeiroAcesso()
    {
        // Verifica se já existe usuário
        $existeUsuario = Usuario::count();

        if ($existeUsuario > 0) {
            die('Sistema já configurado.');
        }

        $existeSituacao = Situacao::count();
        if ($existeSituacao < 1) { 
            // Inserir situações
            Situacao::insert([
                [
                    'id' => 1,
                    'nome' => 'Ativo'
                ],
                [
                    'id' => 2,
                    'nome' => 'Inativo'
                ],
                [
                    'id' => 3,
                    'nome' => 'Excluído'
                ]
            ]);
        }

        // Criar usuário admin
        Usuario::create([
            'nome' => 'Administrador',
            'email' => 'admin@admin.com',
            'senha' => password_hash('123456', PASSWORD_DEFAULT),
            'situacao_id' => 1
        ]);

        echo '
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Primeiro Acesso</title>

        <script src="https://cdn.tailwindcss.com"></script>
    </head>

    <body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">

        <div class="bg-white rounded-3xl shadow-xl p-8 max-w-md w-full">

            <div class="text-center mb-6">
                <div class="w-20 h-20 mx-auto rounded-full bg-emerald-100 flex items-center justify-center mb-4">
                    <span class="text-4xl">✅</span>
                </div>

                <h1 class="text-2xl font-bold text-slate-800">
                    Sistema configurado
                </h1>

                <p class="text-slate-500 mt-2">
                    O primeiro acesso foi criado com sucesso.
                </p>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3">

                <div>
                    <p class="text-xs uppercase text-slate-400 font-semibold">
                        E-mail
                    </p>

                    <p class="font-bold text-slate-700">
                        admin@admin.com
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase text-slate-400 font-semibold">
                        Senha
                    </p>

                    <p class="font-bold text-slate-700">
                        123456
                    </p>
                </div>
            </div>

            <a href="/login"
               class="mt-6 w-full inline-flex justify-center items-center bg-indigo-600 hover:bg-indigo-700 transition-colors text-white font-semibold py-3 rounded-2xl">
                Acessar Login
            </a>

        </div>

    </body>
    </html>
    ';
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