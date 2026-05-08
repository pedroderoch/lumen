<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    protected $table = 'fornecedores';

    protected $fillable = [
        'usuario_id',
        'situacao_id',
        'nome',
        'descricao',
        'tipo',
        'documento',
        'email',
        'telefone',
        'chave_pix',
        'foto'
    ];

    /**
     * RELACIONAMENTO: Uma fornecedor PERTENCE a um Usuário
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function situacao()
    {
        return $this->belongsTo(Situacao::class, 'situacao_id');
    }
}