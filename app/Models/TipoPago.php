<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoPago extends Model{
    protected $table = 'tipo_pagos_2';
    protected $primaryKey = 'ID_TIPO_PAGO';
    public $timestamps = false;
}
