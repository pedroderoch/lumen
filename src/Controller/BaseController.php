<?php

namespace App\Controller;

use Twig\Environment;

/**
 * =========================================================================
 * O que é esta classe? (A Analogia da "Caixa de Ferramentas")
 * =========================================================================
 * * Pense no BaseController como uma "Caixa de Ferramentas Padrão" que todo
 * "trabalhador" (seus outros controllers, como UsuarioController) vai receber.
 *
 * 'abstract class' significa "MOLDE":
 * Esta classe é um "molde" de Controller. Você não pode usá-la
 * diretamente (ex: new BaseController()). Ela só serve para ser o "PAI"
 * de outros controllers (ex: class UsuarioController extends BaseController).
 */
abstract class BaseController
{
    /**
     * O "ESPAÇO" NA CAIXA DE FERRAMENTAS
     * * Esta é a propriedade que vai "segurar" a nossa ferramenta Twig.
     * * 'protected' significa: "Eu (o pai) e meus filhos (UsuarioController,
     * HomeController) podemos ver e usar esta variável."
     * (Se fosse 'private', só o pai poderia ver).
     */
    protected Environment $twig;

    /**
     * O "BURACO DA INJEÇÃO" (O Construtor)
     * * Este é o "construtor", a "porta de entrada" da caixa de ferramentas.
     * * O que ele diz é: "Para qualquer um me construir (ou construir um
     * dos meus filhos), a 'fábrica' (o DI Container lá do index.php)
     * PRECISA me entregar uma ferramenta 'Environment' (o Twig) já pronta."
     * * O DI Container faz isso automaticamente.
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;

        /**
         * INJEÇÃO GLOBAL DE DADOS DO USUÁRIO
         * ---------------------------------------------------------------------
         */
        $this->twig->addGlobal('user_logado', [
            'nome'  => $_SESSION['user_nome'] ?? null,
            'nivel' => $_SESSION['user_nivel'] ?? null
        ]);
        
        /**
         * REGISTRO SEGURO DE FUNÇÕES
         * Verificamos se a função já foi registrada pelo Twig para evitar o erro de duplicidade.
         */
        $functions = $this->twig->getFunctions();
        
        if (!isset($functions['session_get'])) {
            $this->twig->addFunction(new \Twig\TwigFunction('session_get', function ($key) {
                return $_SESSION[$key] ?? null;
            }));
        }
    }

    /**
     * O "SUPERPODER" (A Ferramenta de Atalho)
     * * Este é o "superpoder" ou "atalho" principal que esta caixa
     * de ferramentas oferece a todos os seus filhos.
     *
     * 'protected function' significa que só o pai e os filhos podem
     * usar este método.
     */
    protected function render(string $template, array $data = []): void
    {
        /**
         * "USANDO A FERRAMENTA"
         * * Quando um "filho" (como UsuarioController) chama $this->render(...),
         * ele na verdade está usando ESTE método aqui no "pai".
         * * O trabalho deste "atalho" é:
         * 1. Pegar a ferramenta Twig (que guardamos em $this->twig).
         * 2. Usar a ferramenta para renderizar o template
         * (ex: 'home.html.twig').
         * 3. 'echo' (imprimir) o HTML final no navegador.
         */
        echo $this->twig->render($template, $data);
    }
}