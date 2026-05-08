<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = [
        'usuario_id',
        'situacao_id',
        'descricao',
        'data_alerta',
        'data_vencimento',
        'icone',
        'cor'
    ];

    public function situacao()
    {
        return $this->belongsTo(Situacao::class, 'situacao_id');
    }
}