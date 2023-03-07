<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoClienteAire extends Model{
    protected $table = 'tipo_clientes_aire_2';
    protected $primaryKey = 'ID_TIPO_CLIENTE';
    public $timestamps = false;
}
