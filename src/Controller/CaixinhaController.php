<?php
namespace App\Controller;

use App\Model\Caixinha;
use App\Model\CaixinhaMovimentacao;
use App\Model\Usuario;
use App\Model\Situacao;
use Twig\Environment; 

class CaixinhaController extends BaseController
{

    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
    }
    
    /**
     * Listar Caixinhas
     */
    public function list(): void
    {
        $usuarioId = $_SESSION['user_id'];

        $caixinhas = Caixinha::where('usuario_id', $usuarioId)
                            ->where('situacao_id', 1)
                            ->get();

        $dados = [
            'caixinhas' => $caixinhas,
            'total_acumulado' => $caixinhas->sum('valor_atual')
        ];

        $this->render('caixinhas_index.html.twig', $dados);
    }

    public function show(array $args)
    {
        $id = (int) $args['id'];

        $caixinha = Caixinha::with(['movimentacoes' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->where('id', $id)->where('usuario_id', $_SESSION['user_id'])->first();

        $this->render('caixinhas_show.html.twig', ['caixinha' => $caixinha]);
    }


    public function create(): void
    {
        $this->render('caixinhas_form.html.twig', [
            'caixinha' => null,
            'situacoes' => \App\Model\Situacao::all()
        ]);
    }


    public function store()
    {
        //Tratamento de Valores Monetários
        $valorMetaRaw  = $_POST['valor_meta'] ?? '0';
        $valorAtualRaw = $_POST['valor_atual'] ?? '0';

        // Função rápida para limpar (remove ponto de milhar e troca vírgula por ponto)
        $limparMoeda = fn($v) => str_replace(['.', ','], ['', '.'], $v);

        $valorMeta  = (float) $limparMoeda($valorMetaRaw);
        $valorAtual = (float) $limparMoeda($valorAtualRaw);

        $dataLimite = $_POST['data_limite'] ?? null;

        Caixinha::create([
            'usuario_id'  => $_SESSION['user_id'],
            'nome'        => $_POST['nome'],
            'descricao'   => $_POST['descricao'] ?? null,
            'valor_meta'  => $valorMeta,
            'valor_atual' => $valorAtual,
            'data_limite' => $dataLimite,
            'cor'         => $_POST['cor'] ?? '#8A05BE',
            'situacao_id' => 1 
        ]);

        header('Location: /caixinhas');
        exit;
    }

    public function edit(array $params): void
    {
        $id = $params['id'];
        
        $caixinha = Caixinha::find($id);

        if (!$caixinha) {
            header('Location: /caixinhas');
            exit;
        }

        $this->render('caixinhas_form.html.twig', [
            'caixinha' => $caixinha,
            'situacoes' => \App\Model\Situacao::all()
        ]);
    }


    public function update(array $args): void
    {
        $id = (int) $args['id'];
        $caixinha = Caixinha::where('id', $id)->where('usuario_id', $_SESSION['user_id'])->first();

        if (!$caixinha) {
            header('Location: /caixinhas');
            exit;
        }

        // Armazena o Saldo antigo para comparação
        $saldoAntigo = (float) $caixinha->valor_atual;

        // Limpeza e recebimento do novo saldo vindo do formulário
        $novoSaldo = (float) str_replace(['.', ','], ['', '.'], $_POST['valor_atual']);

        // Se o saldo mudou, registramos a movimentação de ajuste
        if ($novoSaldo != $saldoAntigo) {
            $diferenca = $novoSaldo - $saldoAntigo;
            
            CaixinhaMovimentacao::create([
                'caixinha_id' => $id,
                'tipo' => $diferenca > 0 ? 'entrada' : 'saida',
                'valor' => abs($diferenca),
                'observacao' => 'Ajuste manual de saldo via edição'
            ]);
        }

        $caixinha->nome = $_POST['nome'];
        $caixinha->descricao = $_POST['descricao'];
        $caixinha->valor_meta = (float) str_replace(['.', ','], ['', '.'], $_POST['valor_meta']);
        $caixinha->valor_atual = $novoSaldo;
        $caixinha->data_limite = $_POST['data_limite'] ?: null;
        $caixinha->cor = $_POST['cor'];
        $caixinha->save();

        session_flash('success', 'Caixinha atualizada e ajuste registrado!');
        header('Location: /caixinhas');
    }

    public function destroy(array $params): void
    {
        $id = $params['id'];

        $caixinha = Caixinha::find($id);

        if ($caixinha) {
            $caixinha->update([
                'situacao_id' => 3
            ]);
            
            session_flash('success', 'Caixinha removida com sucesso!');
        }

        header('Location: /caixinhas');
        exit;
    }

    public function movimentar()
    {
        $id = (int) $_POST['caixinha_id'];
        $tipo = $_POST['tipo_movimentacao']; // 'entrada' ou 'saida'
        $valorRaw = $_POST['valor'];

        // Limpeza do valor (Padrão BR -> Banco)
        $valor = (float) str_replace(['.', ','], ['', '.'], $valorRaw);
        $caixinha = Caixinha::findOrFail($id);

        CaixinhaMovimentacao::create([
            'caixinha_id' => $id,
            'tipo' => $tipo,
            'valor' => $valor,
            'observacao' => $tipo === 'entrada' ? 'Depósito realizado' : 'Retirada realizada'
        ]);

        if ($tipo === 'entrada') {
            $caixinha->valor_atual += $valor;
        } else {
            $caixinha->valor_atual -= $valor;
        }

        $caixinha->save();

        session_flash('success', 'Movimentação realizada com sucesso!');
        header('Location: /caixinhas');
    }
}