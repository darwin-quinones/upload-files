<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEdad extends Model{
    protected $table = 'tipo_edades_2';
    protected $primaryKey = 'ID_TIPO_EDAD';
    public $timestamps = false;
}
