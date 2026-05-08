<?php
namespace App\Controller;

use App\Model\ContaBancaria;
use App\Model\Situacao;
use Twig\Environment; 

class ContaBancariaController extends BaseController
{
    public function list(): void
    {
        $usuarioId = $_SESSION['user_id'];

        $contas = ContaBancaria::where('usuario_id', $usuarioId)
                            ->where('situacao_id', 1)
                            ->get();

        $dados = [
            'contas' => $contas,
            'total_saldo' => $contas->sum('saldo_atual')
        ];

        $this->render('contas_index.html.twig', $dados);
    }

    public function create(): void
    {
        $this->render('contas_form.html.twig', [
            'conta' => null,
            'situacoes' => \App\Model\Situacao::all()
        ]);
    }

    public function store()
    {

        $valorLimpo = preg_replace('/[^0-9,]/', '', $_POST['saldo_atual']); 
        $saldo_atual = str_replace(',', '.', $valorLimpo);

        ContaBancaria::create([
            'usuario_id'     => $_SESSION['user_id'],
            'situacao_id'    => 1,
            'nome'           => $_POST['nome'],
            'tipo'           => $_POST['tipo'],
            'saldo_atual'    => (float) $saldo_atual,
            'cor'            => $_POST['cor']
        ]);

        session_flash('success', 'Conta Bancária cadastrada com sucesso!');
        header('Location: /contas');
        exit;
    }

    public function edit(array $params): void
    {
 
        $conta = ContaBancaria::where('id', $params['id'])
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if (!$conta) {
            header('Location: /contas');
            exit;
        }
        
        $this->render('contas_form.html.twig', [
            'conta' => $conta,
            'situacoes' => Situacao::all()
        ]);
    }

    public function update(array $args): void
    {
        $id = (int) $args['id'];
        $conta = ContaBancaria::where('id', $id)
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if (!$conta) {
            session_flash('errors', ['Cartão não encontrado.']);
            header('Location: /contas');
            exit;
        }

        $saldo_atual = str_replace(['.', ','], ['', '.'], $_POST['saldo_atual']);

        $conta->nome           = $_POST['nome'];
        $conta->tipo           = $_POST['tipo']; 
        $conta->saldo_atual    = (float) $saldo_atual;
        $conta->cor            = $_POST['cor'];
        $conta->situacao_id    = $_POST['situacao_id'] ?? 1;

        $conta->save();

        session_flash('success', 'Conta Bancárias atualizado com sucesso!');
        header('Location: /contas');
    }

    public function destroy(array $params): void
    {
        $conta = ContaBancaria::where('id', $params['id'])
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if ($conta) {
            $conta->update(['situacao_id' => 3]);
            session_flash('success', 'Conta Bancária removido com sucesso!');
        }

        header('Location: /contas');
        exit;
    }
}