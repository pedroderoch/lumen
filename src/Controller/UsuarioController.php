<?php
namespace App\Controller;

use App\Model\Usuario;
use App\Model\Situacao;
use Twig\Environment; 
use App\Request\UsuarioStoreRequest;
use App\Request\UsuarioUpdateRequest;

class UsuarioController extends BaseController
{

    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
    }
    /**
     * Listar Usuários
     */
    public function list(): void
    {
        
        $usuarios = Usuario::where('situacao_id', '!=', 3)->get();

        $dados = [
            'usuarios' => $usuarios
        ];
        
        $this->render('usuarios_index.html.twig', $dados);
    }

    /**
     * Exibir um único usuário
     */
    public function show(int $id): void
    {
        $usuario = Usuario::with('situacao')->find($id);

        if (!$usuario) {
            session_flash('errors', ['Usuário não encontrado.']);
            header('Location: /usuarios');
            exit;
        }

        $this->render('usuarios_perfil.html.twig', ['usuario' => $usuario]);
    }

    public function create(): void
    {
        $this->render('usuarios_form.html.twig', [
            'usuario' => null,
            'situacoes' => \App\Model\Situacao::all()
        ]);
    }


    public function store(): void
    {
        $request = new UsuarioStoreRequest();

        $dadosValidados = $request->validate($_POST, '/usuarios/cadastrar');

        if (isset($dadosValidados['senha'])) {
            $dadosValidados['senha'] = password_hash($dadosValidados['senha'], PASSWORD_BCRYPT);
        }

        try {
            Usuario::create($dadosValidados);
            session_flash('success', 'Usuário cadastrado com sucesso!');
            header('Location: /usuarios');
            exit;
        } catch (\Exception $e) {
            // Erro de banco de dados (não de validação)
            error_log('Erro BD: ' . $e->getMessage());
            session_flash('errors', ['Erro interno ao salvar.']);
            header('Location: /usuarios/cadastrar');
            exit;
        }
    }

    public function edit(array $params): void
    {
        $id = $params['id'];
        
        $usuario = Usuario::find($id);

        if (!$usuario) {
            header('Location: /usuarios');
            exit;
        }
        
        $this->render('usuarios_form.html.twig', [
            'usuario' => $usuario,
            'situacoes' => \App\Model\Situacao::all()
        ]);
    }

    public function update(array $params): void
    {
        $id = (int) $params['id'];
        $usuario = Usuario::find($id);

        if (!$usuario) {
            header('Location: /usuarios');
            exit;
        }

        $request = new UsuarioUpdateRequest($id);
        $dadosValidados = $request->validate($_POST, '/usuarios/editar/' . $id);
        
        if (empty($dadosValidados['senha'])) {
            unset($dadosValidados['senha']);
        } else {
            $dadosValidados['senha'] = password_hash($dadosValidados['senha'], PASSWORD_BCRYPT);
        }

        try {
            $usuario->update($dadosValidados);
            
            session_flash('success', 'Usuário atualizado com sucesso!');
            header('Location: /usuarios');
            exit;

        } catch (\Exception $e) {
            error_log('Erro BD: ' . $e->getMessage());
            session_flash('errors', ['Erro interno ao atualizar dados.']);
            
            header('Location: /usuarios/editar/' . $id);
            exit;
        }
    }

    public function destroy(array $params): void
    {
        $id = $params['id'];

        $usuario = Usuario::find($id);

        if ($usuario) {
            $usuario->update([
                'situacao_id' => 3
            ]);
            
            session_flash('success', 'Usuário removido com sucesso!');
        }

        header('Location: /usuarios');
        exit;
    }
}