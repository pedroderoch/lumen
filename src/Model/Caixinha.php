<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Caixinha extends Model
{
    protected $table = 'caixinhas';

    protected $fillable = [
        'usuario_id', // O "dono" da caixinha
        'nome',
        'descricao',
        'valor_meta',
        'valor_atual',
        'cor',
        'data_limite',
        'situacao_id'
    ];

    /**
     * Calcula a porcentagem de progresso automaticamente.
     */
    public function getPorcentagemAttribute()
    {
        if ($this->valor_meta <= 0) {
            return 0;
        }

        $calculo = ($this->valor_atual / $this->valor_meta) * 100;

        // Retorna o valor arredondado (ex: 25 em vez de 25.44)
        return round($calculo);
    }

    /**
     * RELACIONAMENTO: Uma caixinha PERTENCE a um Usuário
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * RELACIONAMENTO: Uma caixinha tem uma Situação (Ativa, Concluída, etc.)
     */
    public function situacao()
    {
        return $this->belongsTo(Situacao::class, 'situacao_id');
    }

    public function movimentacoes()
    {
        return $this->hasMany(CaixinhaMovimentacao::class, 'caixinha_id');
    }

    // Dentro da classe Caixinha
    public function setDataLimiteAttribute($value)
    {
        // Se o valor estiver vazio, salva como null no banco
        $this->attributes['data_limite'] = empty($value) ? null : $value;
    }
}