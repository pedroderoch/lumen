<?php

namespace App\Controller;

use App\Model\Lancamento;
use App\Model\Categoria;
use App\Model\ContaBancaria;
use App\Model\CartaoCredito;
use App\Model\Situacao;
use App\Model\Fornecedor;

class LancamentoController extends BaseController
{
    /**
     * GET /lancamentos
     * Lista todos os lançamentos ativos com totais financeiros
     */
    public function list(): void
    {
        $usuarioId = $_SESSION['user_id'];

        $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
        $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

        $lancamentos = Lancamento::where('usuario_id', $usuarioId)
                        ->where('situacao_id', 1)
                        ->whereMonth('data_vencimento', $mes)
                        ->whereYear('data_vencimento', $ano)
                        ->with(['categoria', 'conta', 'cartao', 'fornecedor'])
                        ->orderBy('data_vencimento', 'asc')
                        ->get();

        $totais = [
            'a_pagar'   => $lancamentos->where('tipo', 'saida')->whereIn('status', ['aberto', 'pendente'])->sum('valor'),
            'pago'      => $lancamentos->where('tipo', 'saida')->where('status', 'pago')->sum('valor'),
            'a_receber' => $lancamentos->where('tipo', 'entrada')->whereIn('status', ['aberto', 'pendente'])->sum('valor'),
            'recebido'  => $lancamentos->where('tipo', 'entrada')->where('status', 'pago')->sum('valor'),
            'saldo_mes' => $lancamentos->where('tipo', 'entrada')->sum('valor') - $lancamentos->where('tipo', 'saida')->sum('valor')
        ];

        $dataAtual = new \DateTime("$ano-$mes-01");
        $anterior = (clone $dataAtual)->modify('-1 month');
        $proximo = (clone $dataAtual)->modify('+1 month');

        $this->render('lancamentos_index.html.twig', [
            'lancamentos'   => $lancamentos,
            'totais'        => $totais,
            'titulo_mes'    => $this->getNomeMes($mes) . " " . $ano,
            'mes_anterior'  => $anterior->format('m'),
            'ano_anterior'  => $anterior->format('Y'),
            'mes_proximo'   => $proximo->format('m'),
            'ano_proximo'   => $proximo->format('Y')
        ]);
    }

    private function getNomeMes(int $mes): string {
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        return $meses[$mes];
    }

    public function show(array $params): void
    {
        $lancamento = Lancamento::where('id', $params['id'])
            ->where('usuario_id', $_SESSION['user_id'])
            ->with(['categoria', 'fornecedor', 'conta', 'cartao'])
            ->first();

        if (!$lancamento) {
            header('Location: /lancamentos');
            exit;
        }

        $this->render('lancamentos_show.html.twig', ['lancamento' => $lancamento]);
    }

    /**
     * GET /lancamentos/cadastrar
     */
    public function create(): void
    {
        $usuarioId = $_SESSION['user_id'];

        $this->render('lancamentos_form.html.twig', [
            'lancamento'   => new Lancamento(),
            'categorias'   => Categoria::where('usuario_id', $usuarioId)->where('situacao_id', 1)->get(),
            'contas'       => ContaBancaria::where('usuario_id', $usuarioId)->where('situacao_id', 1)->get(),
            'cartoes'      => CartaoCredito::where('usuario_id', $usuarioId)->where('situacao_id', 1)->get(),
            'fornecedores' => Fornecedor::where('usuario_id', $usuarioId)->where('situacao_id', 1)->get()
        ]);
    }

    /**
     * POST /lancamentos/criar
     */
    public function store(): void
    {
        $valorLimpo = preg_replace('/[^0-9,]/', '', $_POST['valor']);
        $valor = (float) str_replace(',', '.', $valorLimpo);

        // Lógica para o Cartão de Crédito
        $formaPagamento = $_POST['forma_pagamento'] ?? 'pix';
        $cartaoId = ($formaPagamento === 'cartao_credito') ? ($_POST['cartao_id'] ?: null) : null;

        Lancamento::create([
            'usuario_id'      => $_SESSION['user_id'],
            'descricao'       => $_POST['descricao'],
            'fornecedor_id'   => $_POST['fornecedor_id'] ?: null,
            'categoria_id'    => $_POST['categoria_id'],
            'valor'           => $valor,
            'tipo'            => $_POST['tipo'],
            'forma_pagamento' => $formaPagamento,
            'cartao_id'       => $cartaoId, // Captura do ID selecionado
            'conta_id'        => $_POST['conta_id'] ?? null,
            'data_transacao'  => $_POST['data_transacao'], // NOVO CAMPO
            'data_vencimento' => $_POST['data_vencimento'],
            'data_pagamento'  => $_POST['status'] === 'pago' ? ($_POST['data_pagamento'] ?: date('Y-m-d')) : null,
            'status'          => $_POST['status'] ?? 'aberto',
            'situacao_id'     => 1,
            'observacao'      => $_POST['observacao'] ?? null
        ]);

        session_flash('success', 'Lançamento registrado com sucesso!');
        header('Location: /lancamentos');
        exit;
    }

    /**
     * GET /lancamentos/editar/{id}
     */
    public function edit(array $params): void
    {
        $usuarioId = $_SESSION['user_id'];
        $lancamento = Lancamento::where('id', $params['id'])->where('usuario_id', $usuarioId)->first();

        if (!$lancamento) {
            header('Location: /lancamentos');
            exit;
        }

        $this->render('lancamentos_form.html.twig', [
            'lancamento'   => $lancamento,
            'categorias'   => Categoria::where('usuario_id', $usuarioId)->where('situacao_id', 1)->get(),
            'contas'       => ContaBancaria::where('usuario_id', $usuarioId)->where('situacao_id', 1)->get(),
            'cartoes'      => CartaoCredito::where('usuario_id', $usuarioId)->where('situacao_id', 1)->get(),
            'fornecedores' => Fornecedor::where('usuario_id', $usuarioId)->where('situacao_id', 1)->get(),
            'situacoes'    => Situacao::all()
        ]);
    }

    /**
     * POST /lancamentos/atualizar/{id}
     */
    public function update(array $params): void
    {
        $lancamento = Lancamento::where('id', (int)$params['id'])
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if (!$lancamento) {
            session_flash('errors', ['Lançamento não encontrado.']);
            header('Location: /lancamentos');
            exit;
        }

        $valorLimpo = preg_replace('/[^0-9,]/', '', $_POST['valor']);
        $valor = (float) str_replace(',', '.', $valorLimpo);

        $formaPagamento = $_POST['forma_pagamento'];
        $cartaoId = ($formaPagamento === 'cartao_credito') ? ($_POST['cartao_id'] ?: null) : null;

        $lancamento->update([
            'descricao'       => $_POST['descricao'],
            'fornecedor_id'   => $_POST['fornecedor_id'] ?: null,
            'valor'           => $valor,
            'categoria_id'    => $_POST['categoria_id'],
            'tipo'            => $_POST['tipo'],
            'forma_pagamento' => $formaPagamento,
            'cartao_id'       => $cartaoId, // Se mudar para PIX, aqui limpa para NULL
            'data_transacao'  => $_POST['data_transacao'], 
            'data_vencimento' => $_POST['data_vencimento'],
            'data_pagamento'  => $_POST['status'] === 'pago' ? ($_POST['data_pagamento'] ?: $lancamento->data_pagamento) : null,
            'status'          => $_POST['status'],
            'observacao'      => $_POST['observacao'] ?? null,
            'situacao_id'     => $_POST['situacao_id'] ?? 1
        ]);

        session_flash('success', 'Lançamento atualizado!');
        header('Location: /lancamentos');
        exit;
    }

    /**
     * POST /lancamentos/excluir/{id}
     */
    public function destroy(array $params): void
    {
        $lancamento = Lancamento::where('id', $params['id'])
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if ($lancamento) {
            $lancamento->update(['situacao_id' => 3]);
            session_flash('success', 'Lançamento excluído com sucesso.');
        }

        header('Location: /lancamentos');
        exit;
    }
}