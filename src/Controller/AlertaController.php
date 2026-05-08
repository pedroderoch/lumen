<?php

namespace App\Controller;

use App\Model\Alerta;
use App\Model\Situacao;

class AlertaController extends BaseController
{
    /**
     * GET /alertas
     * Lista todos os alertas ativos do usuário
     */
    public function list(): void
    {
        $usuarioId = $_SESSION['user_id'];

        $alertas = Alerta::where('usuario_id', $usuarioId)
                        ->where('situacao_id', '!=', 3)
                        ->orderBy('data_vencimento', 'asc')
                        ->get();

        $this->render('alertas_index.html.twig', [
            'alertas' => $alertas
        ]);
    }

    /**
     * GET /alertas/{id}
     * Visualiza os detalhes de um alerta específico
     */
    public function show(array $params): void
    {
        $alerta = Alerta::where('id', $params['id'])
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if (!$alerta) {
            header('Location: /alertas');
            exit;
        }

        $this->render('alertas_show.html.twig', [
            'alerta' => $alerta
        ]);
    }

    /**
     * GET /alertas/cadastrar
     * Abre o formulário de criação
     */
    public function create(): void
    {
        $this->render('alertas_form.html.twig', [
            'alerta' => null,
            'situacoes' => Situacao::all()
        ]);
    }

    /**
     * POST /alertas/criar
     * Processa a criação do novo alerta
     */
    public function store(): void
    {
        // Validação básica
        if (empty($_POST['descricao']) || empty($_POST['data_alerta'])) {
            session_flash('errors', ['Descrição e Data do Alerta são obrigatórios.']);
            header('Location: /alertas/cadastrar');
            exit;
        }

        Alerta::create([
            'usuario_id'      => $_SESSION['user_id'],
            'situacao_id'     => 1,
            'descricao'       => $_POST['descricao'],
            'data_alerta'     => $_POST['data_alerta'],
            'data_vencimento' => $_POST['data_vencimento'] ?: null,
            'icone'           => $_POST['icone'] ?: 'ph-bell',
            'cor'             => $_POST['cor'] ?: '#eab308'
        ]);

        session_flash('success', 'Alerta configurado com sucesso!');
        header('Location: /alertas');
        exit;
    }

    /**
     * GET /alertas/editar/{id}
     * Abre o formulário de edição
     */
    public function edit(array $params): void
    {
        $alerta = Alerta::where('id', $params['id'])
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if (!$alerta) {
            header('Location: /alertas');
            exit;
        }
        
        $this->render('alertas_form.html.twig', [
            'alerta' => $alerta,
            'situacoes' => Situacao::all()
        ]);
    }

    /**
     * POST /alertas/atualizar/{id}
     * Processa a atualização dos dados
     */
    public function update(array $params): void
    {
        $alerta = Alerta::where('id', (int)$params['id'])
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if (!$alerta) {
            session_flash('errors', ['Alerta não encontrado.']);
            header('Location: /alertas');
            exit;
        }

        $alerta->descricao       = $_POST['descricao'];
        $alerta->data_alerta     = $_POST['data_alerta'];
        $alerta->data_vencimento = $_POST['data_vencimento'] ?: null;
        $alerta->icone           = $_POST['icone'];
        $alerta->cor             = $_POST['cor'];
        $alerta->situacao_id     = $_POST['situacao_id'] ?? 1;

        $alerta->save();

        session_flash('success', 'Alerta atualizado com sucesso!');
        header('Location: /alertas');
        exit;
    }

    /**
     * POST /alertas/excluir/{id}
     * Realiza a exclusão lógica
     */
    public function destroy(array $params): void
    {
        $alerta = Alerta::where('id', $params['id'])
            ->where('usuario_id', $_SESSION['user_id'])
            ->first();

        if ($alerta) {
            $alerta->update(['situacao_id' => 3]);
            session_flash('success', 'Alerta removido com sucesso!');
        }

        header('Location: /alertas');
        exit;
    }
}