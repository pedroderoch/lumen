<?php
// C:\financeiro\container.php

// Importa as classes que vamos usar
use DI\ContainerBuilder;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\TwigFunction;

// Cria o "construtor" do contêiner
$containerBuilder = new ContainerBuilder();

// Adiciona as "definições" (as "receitas" de como construir as coisas)
$containerBuilder->addDefinitions([
    
    // Define como construir o Twig (Twig\Environment)
    Environment::class => function () {
        $loader = new FilesystemLoader(__DIR__ . '/views');
        
        $twig = new Environment($loader, [
             'cache' => false, 
             'debug' => true
        ]);

        $twig->addExtension(new DebugExtension());
        $twig->addFunction(new TwigFunction('session_has', 'session_has'));
        $twig->addFunction(new TwigFunction('session_get', 'session_get'));
        $twig->addFunction(new TwigFunction('csrf_token', 'generate_csrf_token'));
        
        return $twig;
    },

]);

return $containerBuilder->build();