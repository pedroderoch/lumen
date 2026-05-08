<?php

// 1. Inclui o autoloader
require_once __DIR__ . '/vendor/autoload.php';

// 2. Importa o "Capsule" do Eloquent
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory as ValidatorFactory;
use Illuminate\Container\Container;
use Illuminate\Validation\DatabasePresenceVerifier;

// --- 1. CARREGAR O .ENV ---
// Isso lê o arquivo .env e disponibiliza as variáveis em $_ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// ==============================================================
// TRATAMENTO DE ERROS (WHOOPS)
// ==============================================================
// Só ativamos se estivermos em modo de DEBUG (definido no .env)
// Se a chave não existir, assumimos 'false' por segurança.
if ($_ENV['APP_DEBUG'] === 'true') {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}
// ==============================================================


// 3. Cria uma nova instância do Capsule
$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_DATABASE'],
    'username'  => $_ENV['DB_USERNAME'],
    'password'  => $_ENV['DB_PASSWORD'],
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);

// 5. "Liga" o Eloquent globalmente
// Isso torna o Eloquent acessível de qualquer lugar
$capsule->setAsGlobal();

// 6. "Boota" (Inicializa) o Eloquent
$capsule->bootEloquent();

// --- PARTE 2: CONFIGURAR O VALIDADOR (AS NOVAS FUNCTIONS) ---
// (Este é o código que adicionamos para a Validação)
$container = new Container();
// 'singleton' espera uma "receita" (Closure)
// 'instance' aceita um "objeto pronto".
// Nós já temos o objeto ($container), então usamos 'instance'.
// $container->singleton('app', $container);
$container->instance('app', $container);

$filesystem = new Filesystem();
$loader = new FileLoader($filesystem, 'lang'); 
$translator = new Translator($loader, 'en');
$validatorFactory = new ValidatorFactory($translator, $container);

// 1. Crie o "Verificador de Presença" (o "Tradutor")
$presenceVerifier = new DatabasePresenceVerifier($capsule->getDatabaseManager());

// 2. "Apresente" o Verificador para a Fábrica de Validação.
$validatorFactory->setPresenceVerifier($presenceVerifier);

// Função "helper" global para criar um validador
function validator(array $data, array $rules, array $mensagens = [])
{
    global $validatorFactory;
    return $validatorFactory->make($data, $rules, $mensagens);
}

// Funções "helper" globais para a Sessão Flash
function session_flash($key, $value)
{
    $_SESSION[$key] = $value;
}

function session_get($key, $default = null)
{
    $value = $_SESSION[$key] ?? $default;
    unset($_SESSION[$key]); // Apaga a mensagem "flash" após ser lida
    return $value;
}

function session_has($key)
{
    return isset($_SESSION[$key]);
}

/**
 * GERA O TOKEN CSRF
 * 1. Verifica se já existe um token na sessão.
 * 2. Se não existir, cria um token aleatório, seguro e longo.
 * 3. Salva na sessão e o retorna.
 */
function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        // bin2hex(random_bytes(32)) cria uma string de 64 caracteres
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * VALIDA O TOKEN CSRF
 * 1. Pega o token enviado pelo formulário.
 * 2. Compara ele com o token guardado na sessão.
 * 3. Usa hash_equals() para uma comparação segura (previne "timing attacks").
 */
function validate_csrf_token($token)
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}