<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ContaBancaria extends Model
{
    protected $table = 'contas_bancarias';

    protected $fillable = [
        'usuario_id',
        'situacao_id',
        'nome',
        'tipo',
        'saldo_atual',
        'cor'
    ];

    public function situacao()
    {
        return $this->belongsTo(Situacao::class, 'situacao_id');
    }

    /**
     * RELACIONAMENTO: Uma Conta PERTENCE a um Usuário
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}