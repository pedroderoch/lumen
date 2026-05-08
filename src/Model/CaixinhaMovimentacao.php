<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CaixinhaMovimentacao extends Model
{
    protected $table = 'caixinhas_movimentacoes';

    protected $fillable = [
        'caixinha_id', 
        'tipo', 
        'valor', 
        'observacao'
    ];

    public function caixinha()
    {
        return $this->belongsTo(Caixinha::class, 'caixinha_id');
    }
}