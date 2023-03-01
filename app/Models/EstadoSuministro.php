<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class EstadoSuministro extends Model
{
  protected $table = 'estados_suministro_2';
  protected $primaryKey = 'ID_ESTADO_SUMINISTRO';
  public $timestamps = false;
}
