<?php

use App\Controller\AlertaController;
use App\Controller\AuthController;
use App\Controller\CaixinhaController;
use App\Controller\CartaoCreditoController;
use App\Controller\CategoriaController;
use App\Controller\ContaBancariaController;
use App\Controller\FornecedorController;
use App\Controller\LancamentoController;
use App\Controller\HomeController;
use App\Controller\UsuarioController;

// A função simpleDispatcher é do FastRoute
return FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    
    // HOME 
    $r->addRoute('GET', '/', ['App\Controller\HomeController', 'index']);

   // ROTAS DE AUTENTICACAO
    $r->addRoute('GET', '/login', ['App\Controller\AuthController', 'index']);
    $r->addRoute('POST', '/login', ['App\Controller\AuthController', 'login']);
    $r->addRoute('GET', '/logout', ['App\Controller\AuthController', 'logout']);

    // ROTAS DE CAIXINHAS
    $r->addRoute('GET', '/caixinhas', [CaixinhaController::class, 'list']);
    $r->addRoute('GET', '/caixinhas/cadastrar', [CaixinhaController::class, 'create']);
    $r->addRoute('POST', '/caixinhas/criar', [CaixinhaController::class, 'store']);
    $r->addRoute('GET', '/caixinhas/editar/{id:\d+}', [CaixinhaController::class, 'edit']);
    $r->addRoute('POST', '/caixinhas/atualizar/{id:\d+}', [CaixinhaController::class, 'update']);
    $r->addRoute('POST', '/caixinhas/excluir/{id:\d+}', [CaixinhaController::class, 'destroy']);
    // No seu index.php (FastRoute)
    $r->addRoute('POST', '/caixinhas/movimentar', ['App\Controller\CaixinhaController', 'movimentar']);
    // No seu public/index.php
    $r->addRoute('GET', '/caixinhas/show/{id:\d+}', ['App\Controller\CaixinhaController', 'show']);

    // ROTAS DE FORNECEDORES
    $r->addRoute('GET', '/fornecedores', [FornecedorController::class, 'list']);
    $r->addRoute('GET', '/fornecedor/{id:\d+}', [FornecedorController::class, 'show']);
    $r->addRoute('GET', '/fornecedores/cadastrar', [FornecedorController::class, 'create']);
    $r->addRoute('POST', '/fornecedores/criar', [FornecedorController::class, 'store']);
    $r->addRoute('GET', '/fornecedores/editar/{id:\d+}', [FornecedorController::class, 'edit']);
    $r->addRoute('POST', '/fornecedores/atualizar/{id:\d+}', [FornecedorController::class, 'update']);
    $r->addRoute('POST', '/fornecedores/excluir/{id:\d+}', [FornecedorController::class, 'destroy']);
        
    // ROTAS DE USUARIOS
    $r->addRoute('GET', '/usuarios', [UsuarioController::class, 'list']);
    // $r->addRoute('GET', '/usuario/{id:\d+}', [UsuarioController::class, 'show']);
    $r->addRoute('GET', '/usuarios/cadastrar', [UsuarioController::class, 'create']);
    $r->addRoute('POST', '/usuarios/criar', [UsuarioController::class, 'store']);
    $r->addRoute('GET', '/usuarios/editar/{id:\d+}', [UsuarioController::class, 'edit']);
    $r->addRoute('POST', '/usuarios/atualizar/{id:\d+}', [UsuarioController::class, 'update']);
    $r->addRoute('POST', '/usuarios/excluir/{id:\d+}', [UsuarioController::class, 'destroy']);

    // ROTAS DE CARTOES DE CRÉDITO
    $r->addRoute('GET', '/cartoes', [CartaoCreditoController::class, 'list']);
    $r->addRoute('GET', '/cartoes/{id:\d+}', [CartaoCreditoController::class, 'show']);
    $r->addRoute('GET', '/cartoes/cadastrar', [CartaoCreditoController::class, 'create']);
    $r->addRoute('POST', '/cartoes/criar', [CartaoCreditoController::class, 'store']);
    $r->addRoute('GET', '/cartoes/editar/{id:\d+}', [CartaoCreditoController::class, 'edit']);
    $r->addRoute('POST', '/cartoes/atualizar/{id:\d+}', [CartaoCreditoController::class, 'update']);
    $r->addRoute('POST', '/cartoes/excluir/{id:\d+}', [CartaoCreditoController::class, 'destroy']);
    // Rota para o Extrato Detalhado do Cartão
    $r->addRoute('GET', '/cartoes/extrato/{id:\d+}', [CartaoCreditoController::class, 'extrato']);

    // ROTAS DE CONTAS BANCÁRIAS
    $r->addRoute('GET', '/contas', [ContaBancariaController::class, 'list']);
    $r->addRoute('GET', '/contas/{id:\d+}', [ContaBancariaController::class, 'show']);
    $r->addRoute('GET', '/contas/cadastrar', [ContaBancariaController::class, 'create']);
    $r->addRoute('POST', '/contas/criar', [ContaBancariaController::class, 'store']);
    $r->addRoute('GET', '/contas/editar/{id:\d+}', [ContaBancariaController::class, 'edit']);
    $r->addRoute('POST', '/contas/atualizar/{id:\d+}', [ContaBancariaController::class, 'update']);
    $r->addRoute('POST', '/contas/excluir/{id:\d+}', [ContaBancariaController::class, 'destroy']);

    // ROTAS DE CATEGORIAS
    $r->addRoute('GET', '/categorias', [CategoriaController::class, 'list']);
    $r->addRoute('GET', '/categorias/{id:\d+}', [CategoriaController::class, 'show']);
    $r->addRoute('GET', '/categorias/cadastrar', [CategoriaController::class, 'create']);
    $r->addRoute('POST', '/categorias/criar', [CategoriaController::class, 'store']);
    $r->addRoute('GET', '/categorias/editar/{id:\d+}', [CategoriaController::class, 'edit']);
    $r->addRoute('POST', '/categorias/atualizar/{id:\d+}', [CategoriaController::class, 'update']);
    $r->addRoute('POST', '/categorias/excluir/{id:\d+}', [CategoriaController::class, 'destroy']);

    // ROTAS DE ALERTAS
    $r->addRoute('GET', '/alertas', [AlertaController::class, 'list']);
    $r->addRoute('GET', '/alertas/{id:\d+}', [AlertaController::class, 'show']);
    $r->addRoute('GET', '/alertas/cadastrar', [AlertaController::class, 'create']);
    $r->addRoute('POST', '/alertas/criar', [AlertaController::class, 'store']);
    $r->addRoute('GET', '/alertas/editar/{id:\d+}', [AlertaController::class, 'edit']);
    $r->addRoute('POST', '/alertas/atualizar/{id:\d+}', [AlertaController::class, 'update']);
    $r->addRoute('POST', '/alertas/excluir/{id:\d+}', [AlertaController::class, 'destroy']);

    // ROTAS DE LANCAMENTOS
    $r->addRoute('GET', '/lancamentos', [LancamentoController::class, 'list']);
    $r->addRoute('GET', '/lancamentos/{id:\d+}', [LancamentoController::class, 'show']);
    $r->addRoute('GET', '/lancamentos/cadastrar', [LancamentoController::class, 'create']);
    $r->addRoute('POST', '/lancamentos/criar', [LancamentoController::class, 'store']);
    $r->addRoute('GET', '/lancamentos/editar/{id:\d+}', [LancamentoController::class, 'edit']);
    $r->addRoute('POST', '/lancamentos/atualizar/{id:\d+}', [LancamentoController::class, 'update']);
    $r->addRoute('POST', '/lancamentos/excluir/{id:\d+}', [LancamentoController::class, 'destroy']);

});