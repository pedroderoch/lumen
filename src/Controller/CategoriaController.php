<?php
namespace App\Controller;

use App\Model\Categoria;
use App\Model\Situacao;
use Twig\Environment; 

class CategoriaController extends BaseController
{
    public function list(): void
    {
        $usuarioId = $_SESSION['user_id'];

        $categorias = Categoria::where('usuario_id', $usuarioId)
                            ->where('situacao_id', 1)
                            ->get();
        $dados = [
            'categorias' => $categorias
        ];

        $this->render('categorias_index.html.twig', $dados);
    }

    public function create(): void
    {
        $this->render('categorias_form.html.twig', [
            'categoria' => null,
            'situacoes' => \App\Model\Situacao::all()
        ]);
    }

    public function store()
    {
        if (empty($_POST['nome']) || empty($_POST['tipo'])) {
            session_flash('errors', ['O nome e o tipo da categoria são obrigatórios.']);
            header('Location: /categorias/cadastrar');
            exit;
        }

        // Se for uma 'entrada' (receita), a natureza sempre será 'variavel' por padrão,
        $natureza = ($_POST['tipo'] === 'entrada') ? 'variavel' : ($_POST['natureza'] ?? 'variavel');

        Categoria::create([
            'usuario_id'  => $_SESSION['user_id'],
            'situacao_id' => 1, // 1 = Ativa
            'nome'        => $_POST['nome'],
            'tipo'        => $_POST['tipo'], // entrada ou saida
            'natureza'    => $natureza,      // fixo ou variavel
            'icone'       => $_POST['icone'] ?? 'ph-tag',
            'cor'         => $_POST['cor'] ?? '#6c757d'
        ]);

        session_flash('success', 'Categoria cadastrada com sucesso!');
        header('Location: /categorias');
        exit;
    }

    public function edit(array $params): void
    {
 
        $categoria = Categoria::where('id', $params['id'])
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if (!$categoria) {
            header('Location: /categorias');
            exit;
        }
        
        $this->render('categorias_form.html.twig', [
            'categoria' => $categoria,
            'situacoes' => Situacao::all()
        ]);
    }

    public function update(array $args): void
    {
        $id = (int) $args['id'];
        $categoria = Categoria::where('id', $id)
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if (!$categoria) {
            session_flash('errors', ['Categoria não encontrada.']);
            header('Location: /categorias');
            exit;
        }

        $categoria->nome           = $_POST['nome'];
        $categoria->tipo           = $_POST['tipo']; 
        $categoria->natureza       = $_POST['natureza'];
        $categoria->icone          = $_POST['icone'];
        $categoria->cor            = $_POST['cor'];

        $categoria->save();

        session_flash('success', 'Categoria atualizada com sucesso!');
        header('Location: /categorias');
    }

    public function destroy(array $params): void
    {
        $categoria = Categoria::where('id', $params['id'])
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if ($categoria) {
            $categoria->update(['situacao_id' => 3]);
            session_flash('success', 'Categoria removida com sucesso!');
        }

        header('Location: /categorias');
        exit;
    }
}