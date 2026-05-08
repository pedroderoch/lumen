<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CartaoCredito extends Model
{
    protected $table = 'cartoes_credito';

    protected $fillable = [
        'usuario_id',
        'situacao_id',
        'nome',
        'limite',
        'dia_vencimento',
        'dia_fechamento',
        'cor'
    ];

    public function situacao()
    {
        return $this->belongsTo(Situacao::class, 'situacao_id');
    }

    /**
     * RELACIONAMENTO: Um cartao PERTENCE a um Usuário
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}