<?php

namespace App\Controller;

use App\Model\ContaBancaria;
use App\Model\Lancamento;
use App\Model\CartaoCredito;
use Illuminate\Database\Capsule\Manager as Capsule;



class HomeController extends BaseController
{
    /**
     * TELA PRINCIPAL (DASHBOARD)
     */
    public function index()
    {
        $usuarioId = $_SESSION['user_id'];

        // Centraliza os dados chamando os métodos e retornando os dados para Dashboard
        $dados = [
            'saldo_em_contas'   => $this->getSaldoRealContas($usuarioId),
            'total_pagar' => $this->getTotalPagarMes($usuarioId),
            'total_baixado' => $this->getTotalBaixadosMes($usuarioId),
            'total_aberto' => $this->getTotalAbertasMes($usuarioId),
            'porcentagem_pagas' => $this->getPorcentagemPagas($usuarioId),
            'porcentagem_recebidas' => $this->getPorcentagemRecebidas($usuarioId),
            'total_receber' => $this->getTotalReceber($usuarioId),
            'total_recebido' => $this->getTotalRecebidosMes($usuarioId),
            'total_aberto_receber' => $this->getTotalParaReceberMes($usuarioId),
            'saldo_mensal' => $this->getResultadosMes($usuarioId),
            'dados_cartao' => $this->getDadosCartaoCredito($usuarioId),
            'gastos_categoria' => $this->getGastosPorCategoria($usuarioId),
            'ultimos_lancamentos' => $this->getBuscaUltimosLancamentos($usuarioId),
            'mes_atual' => $this->getMesAtualExtenso()
        ];

        $this->render('dashboard.html.twig', $dados);
    }

    /**
     * Calcula o saldo real somando as tabelas de contas
     */
    private function getSaldoRealContas(int $userId): float
    {
        return (float) ContaBancaria::where('usuario_id', $userId)
                        ->where('situacao_id', 1)
                        ->sum('saldo_atual');
    }

    /**
     * Calcula o total de DESPESAS para o mês atual
     */
    private function getTotalPagarMes(int $userId): float
    {
        return (float) Lancamento::where('usuario_id', $userId)
                        ->where('tipo', 'saida')
                        ->where('situacao_id', 1)
                        ->whereMonth('data_vencimento', date('m')) // Mês atual
                        ->whereYear('data_vencimento', date('Y'))  // Ano atual
                        ->sum('valor');
    }

    /**
     * Calcula o total de DESPESAS pagas para o mês atual
     */
    private function getTotalBaixadosMes(int $userId): float
    {
        return (float) Lancamento::where('usuario_id', $userId)
                        ->where('tipo', 'saida')
                        ->where('status', '=', 'pago') 
                        ->where('situacao_id', 1) 
                        ->whereMonth('data_vencimento', date('m')) // Mês atual
                        ->whereYear('data_vencimento', date('Y'))  // Ano atual
                        ->sum('valor');
    }

    /**
     * Calcula o total de DESPESAS que faltam ser pagas para o mês atual
     */
    private function getTotalAbertasMes(int $userId): float
    {
        return (float) Lancamento::where('usuario_id', $userId)
                        ->where('tipo', 'saida')
                        ->where('status', '!=', 'pago') 
                        ->where('situacao_id', 1) 
                        ->whereMonth('data_vencimento', date('m')) // Mês atual
                        ->whereYear('data_vencimento', date('Y'))  // Ano atual
                        ->sum('valor');
    }

    /**
     * Calcula % de despesas PAGAS
     */
    private function getPorcentagemPagas(int $userId): float
    {
        $totalPagar = $this->getTotalPagarMes($userId);
        $totalPago  = $this->getTotalBaixadosMes($userId);

        if ($totalPagar <= 0) {
            return 0;
        }

        return round(($totalPago / $totalPagar) * 100, 2);
    }


    /**
     * Calcula o total de RECEITAS para o mês atual
     */
    private function getTotalReceber(int $userId): float
    {
        return (float) Lancamento::where('usuario_id', $userId)
                        ->where('tipo', 'entrada') 
                        ->where('situacao_id', 1)
                        ->whereMonth('data_vencimento', date('m')) // Mês atual
                        ->whereYear('data_vencimento', date('Y'))  // Ano atual
                        ->sum('valor');
    }


    /**
     * Calcula o total de RECEITAS recebidas para o mês atual
     */
    private function getTotalRecebidosMes(int $userId): float
    {
        return (float) Lancamento::where('usuario_id', $userId)
                        ->where('tipo', 'entrada')
                        ->where('status', '=', 'pago')
                        ->where('situacao_id', 1)
                        ->whereMonth('data_vencimento', date('m')) // Mês atual
                        ->whereYear('data_vencimento', date('Y'))  // Ano atual
                        ->sum('valor');
    }

    /**
     * Calcula o total de RECEITAS que faltam ser recebidas para o mês atual
     */
    private function getTotalParaReceberMes(int $userId): float
    {
        return (float) Lancamento::where('usuario_id', $userId)
                        ->where('tipo', 'entrada') 
                        ->where('status', '!=', 'pago') 
                        ->where('situacao_id', 1)
                        ->whereMonth('data_vencimento', date('m')) //mes atual
                        ->whereYear('data_vencimento', date('Y')) // ano atual
                        ->sum('valor');
    }

    /**
     * Calcula % de RECEITAS RECEBIDAS
     */
    private function getPorcentagemRecebidas(int $userId): float
    {
        $totalReceber = $this->getTotalReceber($userId);
        $totalRecebido  = $this->getTotalRecebidosMes($userId);

        // Evita divisão por zero
        if ($totalReceber <= 0) {
            return 0;
        }

        return round(($totalRecebido / $totalReceber) * 100, 2);
    }

    /**
     * Calcula Diferença entre RECEITAS e DESPESAS
     */
    private function getResultadosMes(int $userId): float
    {
        $totalRecebido  = $this->getTotalReceber($userId);
        $totalPagar = $this->getTotalPagarMes($userId);

        return round(($totalRecebido - $totalPagar), 2 );
    }


    /**
     * Retorna os cartões de crédito com dados da fatura
     */
    private function getDadosCartaoCredito(int $userId)
    {
        $cartoes = CartaoCredito::where('usuario_id', $userId)
            ->where('situacao_id', 1)
            ->get();

        foreach ($cartoes as $cartao) {

            $cartao->fatura_atual = Lancamento::where('usuario_id', $userId)
                ->where('tipo', 'saida')
                ->where('forma_pagamento', 'cartao_credito')
                ->where('cartao_id', $cartao->id)
                ->where('situacao_id', 1)
                ->sum('valor');
        }

        return $cartoes;
    }

    /**
     * Retorna total gasto por categoria no mês atual
     */
    private function getGastosPorCategoria(int $userId)
    {

        $totalMes = $this->getTotalPagarMes($userId);

        $categorias = Lancamento::select(
            'categorias.nome',
            'categorias.cor',
            'categorias.icone',
            Capsule::raw('SUM(valor) as total')
        )
        ->join('categorias', 'categorias.id', '=', 'lancamentos.categoria_id')
        ->where('lancamentos.usuario_id', $userId)
        ->where('lancamentos.tipo', 'saida')
        ->where('lancamentos.situacao_id', 1)
        ->whereMonth('lancamentos.data_vencimento', date('m'))
        ->whereYear('lancamentos.data_vencimento', date('Y'))
        ->groupBy('categorias.id', 'categorias.nome', 'categorias.cor', 'categorias.icone')
        ->orderByDesc('total')
        ->get();

        // calcula porcentagem
        foreach ($categorias as $categoria) {

            $categoria->porcentagem = $totalMes > 0
                ? round(($categoria->total / $totalMes) * 100, 1)
                : 0;
        }

        return $categorias;
    }

    private function getBuscaUltimosLancamentos(int $userId){

        $ultimosLancamentos = Lancamento::select(
            'lancamentos.descricao',
            'lancamentos.valor',
            'lancamentos.status',
            'lancamentos.forma_pagamento',
            'lancamentos.data_vencimento',
            'lancamentos.data_pagamento',
            'lancamentos.tipo',
            'categorias.nome as categoria_nome',
            'categorias.icone',
            'fornecedores.nome as fornecedor_nome'
        )
        ->join('categorias', 'categorias.id', '=', 'lancamentos.categoria_id')
        ->join('fornecedores', 'fornecedores.id', '=', 'lancamentos.fornecedor_id')
        ->where('lancamentos.usuario_id', $userId)
        ->where('lancamentos.situacao_id', 1)
        ->orderByDesc('lancamentos.id')
        ->limit(5)
        ->get();

        return $ultimosLancamentos;

    }

    /**
     * Retorna mês atual por extenso
     */
    private function getMesAtualExtenso(): string
    {
        $meses = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        ];

        $mes = (int) date('m');
        $ano = date('Y');

        return $meses[$mes] . ', ' . $ano;
    }







}