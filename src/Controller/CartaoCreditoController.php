<?php
namespace App\Controller;

use App\Model\CartaoCredito;
use App\Model\Situacao;
use App\Model\Lancamento;

use Twig\Environment; 

class CartaoCreditoController extends BaseController
{
    public function list(): void
    {
        $cartoes = CartaoCredito::where('usuario_id', $_SESSION['user_id'])
            ->where('situacao_id', '!=', 3)
            ->get();

        $this->render('cartoes_index.html.twig', [
            'cartoes' => $cartoes,
            'search'  => '',
            'current_page' => 1
        ]);

        // $cartoesFake = [
        //     [
        //         'id' => 1, 'nome' => 'Nubank', 'limite' => 4500.00, 
        //         'dia_fechamento' => 3, 'dia_vencimento' => 10, 'cor' => '#8A05BE'
        //     ],
        //     [
        //         'id' => 2, 'nome' => 'Inter Mastercard', 'limite' => 2000.00, 
        //         'dia_fechamento' => 8, 'dia_vencimento' => 15, 'cor' => '#FF7A00'
        //     ],
        //     [
        //         'id' => 3, 'nome' => 'Itaú Click', 'limite' => 8900.00, 
        //         'dia_fechamento' => 20, 'dia_vencimento' => 27, 'cor' => '#1C325F'
        //     ]
        // ];
    
        // // O "segredo" está aqui: a chave 'cartoes' é o que você usará no {% for cartao in cartoes %}
        // $this->render('cartoes_index.html.twig', [
        //     'cartoes' => $cartoesFake
        // ]);
    }

   /**
 * GET /cartoes/extrato/{id}
 * Exibe as compras detalhadas de um cartão específico no mês selecionado
 */
    public function extrato(array $params): void
    {
        $usuarioId = $_SESSION['user_id'];
        $cartaoId = (int) $params['id'];

        $cartao = CartaoCredito::where('id', $cartaoId)
                    ->where('usuario_id', $usuarioId)
                    ->first();

        if (!$cartao) {
            header('Location: /cartoes');
            exit;
        }

        if (isset($_GET['mes']) && isset($_GET['ano'])) {
            // Navegação manual pelo usuário
            $mes = (int)$_GET['mes'];
            $ano = (int)$_GET['ano'];
        } else {
            // Calcula automaticamente a fatura aberta com base no dia de fechamento
            $hoje     = (int)date('d');
            $mesAtual = (int)date('m');
            $anoAtual = (int)date('Y');

            if ($hoje > $cartao->dia_fechamento) {
                // Fatura já fechou, mostra a próxima
                $dataProxima = new \DateTime("$anoAtual-$mesAtual-01");
                $dataProxima->modify('+1 month');
                $mes = (int)$dataProxima->format('m');
                $ano = (int)$dataProxima->format('Y');
            } else {
                // Ainda dentro do período da fatura atual
                $mes = $mesAtual;
                $ano = $anoAtual;
            }
        }

        // Busca todos os lançamentos vinculados a este cartão neste período
        $compras = Lancamento::where('cartao_id', $cartaoId)
                    ->where('usuario_id', $usuarioId)
                    ->where('situacao_id', 1)
                    ->whereMonth('data_vencimento', $mes)
                    ->whereYear('data_vencimento', $ano)
                    ->with(['categoria', 'fornecedor'])
                    ->orderBy('data_vencimento', 'asc')
                    ->get();

        $totalGasto = $compras->sum('valor');
        $limiteDisponivel = $cartao->limite - $totalGasto;

        // Lógica de Navegação (Mês anterior/próximo)
        $dataRef  = new \DateTime("$ano-$mes-01");
        $anterior = (clone $dataRef)->modify('-1 month');
        $proximo  = (clone $dataRef)->modify('+1 month');

        $this->render('cartoes_extrato.html.twig', [
            'cartao'            => $cartao,
            'compras'           => $compras,
            'total_gasto'       => $totalGasto,
            'limite_disponivel' => $limiteDisponivel,
            'mes'               => $mes,
            'ano'               => $ano,
            'titulo_mes'        => $this->getNomeMes($mes) . " " . $ano,
            'mes_anterior'      => $anterior->format('m'),
            'ano_anterior'      => $anterior->format('Y'),
            'mes_proximo'       => $proximo->format('m'),
            'ano_proximo'       => $proximo->format('Y')
        ]);
    }

    /**
     * Auxiliar para o nome do mês
     */
    private function getNomeMes(int $mes): string 
    {
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        return $meses[$mes];
    }

    public function create(): void
    {
        // Renderiza o formulário
        $this->render('cartoes_form.html.twig', [
            'cartao' => null,
            'situacoes' => \App\Model\Situacao::all()
        ]);
    }

    public function store()
    {
        $limite = str_replace(['.', ','], ['', '.'], $_POST['limite']);

        CartaoCredito::create([
            'usuario_id'     => $_SESSION['user_id'],
            'situacao_id'    => 1,
            'nome'           => $_POST['nome'],
            'limite'         => (float) $limite,
            'dia_vencimento' => $_POST['dia_vencimento'], 
            'dia_fechamento' => $_POST['dia_fechamento'],
            'cor'            => $_POST['cor']
        ]);

        session_flash('success', 'Cartão cadastrado com sucesso!');
        header('Location: /cartoes');
        exit;
    }

    public function edit(array $params): void
    {
 
        $cartao = CartaoCredito::where('id', $params['id'])
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if (!$cartao) {
            header('Location: /cartoes');
            exit;
        }
        
        $this->render('cartoes_form.html.twig', [
            'cartao' => $cartao,
            'situacoes' => Situacao::all()
        ]);
    }

    public function update(array $args): void
    {
        $id = (int) $args['id'];
        $cartao = CartaoCredito::where('id', $id)
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if (!$cartao) {
            session_flash('errors', ['Cartão não encontrado.']);
            header('Location: /cartoes');
            exit;
        }

        $limite = str_replace(['.', ','], ['', '.'], $_POST['limite']);

        $cartao->nome           = $_POST['nome'];
        $cartao->limite         = (float) $limite;
        $cartao->dia_vencimento = $_POST['dia_vencimento']; 
        $cartao->dia_fechamento = $_POST['dia_fechamento'];
        $cartao->cor            = $_POST['cor'];
        $cartao->situacao_id    = $_POST['situacao_id'] ?? 1;

        $cartao->save();

        session_flash('success', 'Cartão atualizado com sucesso!');
        header('Location: /cartoes');
    }

    public function destroy(array $params): void
    {
        $cartao = CartaoCredito::where('id', $params['id'])
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if ($cartao) {
            $cartao->update(['situacao_id' => 3]);
            session_flash('success', 'Cartão removido com sucesso!');
        }

        header('Location: /cartoes');
        exit;
    }

    public function pagarFatura($vars)
    {
        $usuarioId = $_SESSION['user_id'];

        $cartaoId = $vars['id'];

        $mes = $_POST['mes'];
        $ano = $_POST['ano'];
        $dataPagamento = $_POST['data_pagamento'];

        Lancamento::where('usuario_id', $usuarioId)
            ->where('cartao_id', $cartaoId)
            ->where('forma_pagamento', 'cartao_credito')
            ->where('tipo', 'saida')
            ->whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano)
            ->where('status', '!=', 'pago')
            ->update([
                'status' => 'pago',
                'data_pagamento' => $dataPagamento
            ]);

        $_SESSION['success'] = 'Fatura paga com sucesso!';

        header('Location: /cartoes/extrato/' . $cartaoId);

        exit;
    }
}