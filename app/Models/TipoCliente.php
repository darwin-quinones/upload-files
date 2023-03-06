<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCliente extends Model{
    protected $table = 'tipo_clientes_2';
    protected $primaryKey = 'ID_TIPO_CLIENTE';
    public $timestamps = false;
}
