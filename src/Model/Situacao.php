<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Situacao extends Model
{
    protected $table = 'situacoes';
    public $timestamps = true;
    protected $fillable = ['nome'];
}