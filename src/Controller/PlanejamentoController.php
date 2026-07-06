<?php
namespace App\Controller;

use App\Model\Planejamento;
use App\Model\Usuario;
use App\Model\Situacao;
use App\Model\Caixinha;
use Twig\Environment; 

class PlanejamentoController extends BaseController
{

    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
    }

    public function list(): void
    {
        $usuarioId = $_SESSION['user_id'];

        $caixinhas = Caixinha::where('usuario_id', $usuarioId)
            ->where('situacao_id', 1)
            ->get();

        $totalCaixinhas = $caixinhas->sum('valor_atual');

        $this->render('planejamento_index.html.twig', [
            'caixinhas'       => $caixinhas,
            'total_caixinhas' => $totalCaixinhas,
            'planejamento'    => $planejamento ?? null,
        ]);
    }
    
    public function create(): void
    {
        $this->render('fornecedores_form.html.twig', [
            'fornecedor' => null,
            'situacoes' => \App\Model\Situacao::all()
        ]);
    }


    public function store()
    {

        Fornecedor::create([
            'usuario_id'  => $_SESSION['user_id'],
            'situacao_id' => 1,
            'nome'        => $_POST['nome'],
            'descricao'   => $_POST['descricao'] ?? null,
            'tipo'        => $_POST['tipo'], 
            'documento'   => $_POST['documento'],
            'email'       => $_POST['email'],
            'telefone'  => $_POST['telefone'],
            'chave_pix'  => $_POST['chave_pix']
        ]);

        header('Location: /fornecedores');
        exit;
    }

    public function edit(array $params): void
    {
        $id = $params['id'];
        
        $fornecedor = Fornecedor::find($id);

        if (!$fornecedor) {
            header('Location: /fornecedores');
            exit;
        }
        
        $this->render('fornecedores_form.html.twig', [
            'fornecedor' => $fornecedor,
            'situacoes' => \App\Model\Situacao::all()
        ]);
    }

    public function update(array $args): void
    {
        $id = (int) $args['id'];

        $fornecedor = Fornecedor::where('id', $id)
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if (!$fornecedor) {
            session_flash('errors', ['Fornecedor não encontrado ou você não tem permissão.']);
            header('Location: /fornecedores');
            exit;
        }

        $fornecedor->nome      = $_POST['nome'];
        $fornecedor->descricao = $_POST['descricao'] ?? null;
        $fornecedor->tipo      = $_POST['tipo']; 
        $fornecedor->documento = $_POST['documento'];
        $fornecedor->email     = $_POST['email'];
        $fornecedor->telefone  = $_POST['telefone'];
        $fornecedor->chave_pix = $_POST['chave_pix'];
        $fornecedor->situacao_id = $_POST['situacao_id'] ?? 1;

        $fornecedor->save();

        session_flash('success', 'Fornecedor atualizado com sucesso!');
        header('Location: /fornecedores');
    }


    

    public function destroy(array $params): void
    {
        $id = $params['id'];

        $fornecedor = Fornecedor::find($id);

        if ($fornecedor) {
            $fornecedor->update([
                'situacao_id' => 3
            ]);
            
            session_flash('success', 'Fornecedor removido com sucesso!');
        }

        header('Location: /fornecedores');
        exit;
    }

}