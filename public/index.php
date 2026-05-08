<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Inicialização (Composer)
require_once __DIR__ . '/../vendor/autoload.php';

// 2. "Ligar" o Eloquent
require_once __DIR__ . '/../bootstrap.php'; 

// 3. Importar o Contêiner de Injeção de Dependência
$container = require __DIR__ . '/../container.php';

// 4. Importar o Controller de Erro (para o switch)
use App\Controller\ErrorController;

// 5. CARREGAR O "MAPA DE ROTAS"
// O $dispatcher agora é criado pelo routes.php
$dispatcher = require __DIR__ . '/../routes.php';

// 6. Processamento da Requisição 
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);


$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// ============================================================
// 🛡️ SISTEMA DE PROTEÇÃO DE ROTAS (MIDDLEWARE MANUAL)
// ============================================================

// 1. Início da Sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 2. Rotas que NÃO precisam de login
$rotasPublicas = ['/login'];

// 3. Verificação de Identidade
$usuarioLogado = isset($_SESSION['user_id']);

// Lógica de Redirecionamento:
if (!$usuarioLogado && !in_array($uri, $rotasPublicas)) {
    // Se não está logado e tenta acessar QUALQUER coisa (incluindo a /), vai para login
    header('Location: /login');
    exit;
}

if ($usuarioLogado && $uri === '/login') {
    // Se já está logado e tenta ir para o login, volta para o dashboard
    header('Location: /');
    exit;
}

// 7. Tratamento do Resultado 
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $controller = $container->get(ErrorController::class);
        $controller->notFound();
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $controller = $container->get(ErrorController::class);
        $controller->methodNotAllowed();
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        
        [$class, $method] = $handler;

        // ============================================================
        // 🛡️ VERIFICADOR DE TOKEN CSRF (Middleware Manual)
        // ============================================================

        // Verificamos se é uma requisição que MUDA dados (POST)
        if ($httpMethod === 'POST') {
            // Pegamos o token enviado pelo formulário
            $token = $_POST['csrf_token'] ?? '';
            // Usamos nossa função helper para validar
            if (!validate_csrf_token($token)) {
                // Se o token for inválido, paramos tudo.
                // 419 é o código HTTP para "Authentication Timeout"
                // (usado pelo Laravel para falha de CSRF)
                $controller = $container->get(ErrorController::class);
                $controller->csrfError();
                exit;
            }
        }
        // ============================================================

        $controller = $container->get($class);
        
        $controller->$method($vars);
        break;
}