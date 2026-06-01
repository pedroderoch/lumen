<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Lancamento extends Model
{
    protected $table = 'lancamentos';

    /**
     * Campos permitidos para preenchimento em massa (Mass Assignment)
     */
    protected $fillable = [
        'usuario_id',
        'conta_id',
        'cartao_id',      // Referência ao Cartão de Crédito (se aplicável)
        'categoria_id',
        'situacao_id',   // Controle de exclusão lógica (1: Ativo, 3: Excluído)
        'fornecedor_id',
        'descricao',
        'parcela_atual',
        'total_parcelas',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'data_transacao',
        'tipo',            // enum: 'entrada', 'saida'
        'status',          // enum: 'aberto', 'pendente', 'pago'
        'forma_pagamento', // enum: 'dinheiro', 'pix', 'boleto', 'transferencia', 'cartao_credito'
        'observacao'
    ];

    /**
     * RELACIONAMENTOS (Eloquent)
     */

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function conta()
    {
        return $this->belongsTo(ContaBancaria::class, 'conta_id');
    }

    public function cartao()
    {
        return $this->belongsTo(CartaoCredito::class, 'cartao_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function situacao()
    {
        return $this->belongsTo(Situacao::class, 'situacao_id');
    }

    /**
     * ESCOPOS DE BUSCA (Scopes)
     * Facilitam os cálculos no Controller
     */

    public function scopeAtivo($query)
    {
        return $query->where('situacao_id', 1);
    }

    // Filtros para saídas (Pagamentos)
    public function scopeTotalPago($query)
    {
        return $query->where('tipo', 'saida')->where('status', 'pago');
    }

    public function scopeTotalAPagar($query)
    {
        return $query->where('tipo', 'saida')->whereIn('status', ['aberto', 'pendente']);
    }

    // Filtros para entradas (Recebimentos)
    public function scopeTotalRecebido($query)
    {
        return $query->where('tipo', 'entrada')->where('status', 'pago');
    }

    public function scopeTotalAReceber($query)
    {
        return $query->where('tipo', 'entrada')->whereIn('status', ['aberto', 'pendente']);
    }

    // Relacionamento para buscar o nome do Favorecido (Fornecedor)[cite: 1]
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }
}