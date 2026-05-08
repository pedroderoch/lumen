<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = [
        'usuario_id',
        'situacao_id',
        'nome',
        'tipo',
        'natureza',
        'icone',
        'cor'
    ];

    public function situacao()
    {
        return $this->belongsTo(Situacao::class, 'situacao_id');
    }

    
    /**
     * RELACIONAMENTO: Uma Categoria PERTENCE a um Usuário
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}