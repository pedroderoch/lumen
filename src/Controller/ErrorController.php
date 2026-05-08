<?php

namespace App\Controller;

use Twig\Environment; // Importamos o Twig (para o construtor)

/**
 * Este controller vai gerenciar todas as respostas de erro HTTP
 */
class ErrorController extends BaseController
{
    /**
     * O construtor é idêntico aos outros:
     * Recebe o Twig e o repassa para o "pai" (BaseController)
     */
    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
    }

    /**
     * Este método será chamado para gerar a resposta 404
     */
    public function notFound(): void
    {
        // 1. Define o código de resposta HTTP para 404 (Muito importante!)
        http_response_code(404);
        
        // 2. Renderiza a nossa nova view de erro
        $this->render('errors/404.html.twig');
    }

    public function methodNotAllowed(): void
    {
        // 1. Define o código de resposta HTTP para 405
        http_response_code(405);
        
        // 2. Renderiza a nossa nova view de erro 405
        $this->render('errors/405.html.twig');
    }

    /**
     * Este método será chamado para gerar a resposta 419 (Falha de CSRF)
     */
    public function csrfError(): void
    {
        // 1. Define o código de resposta HTTP para 419
        http_response_code(419);
        
        // 2. Renderiza a nossa nova view de erro 419
        $this->render('errors/419.html.twig');
    }
}