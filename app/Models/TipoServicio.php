<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class TipoServicio extends Model
{
  protected $table = 'tipo_servicios_2';
  protected $primaryKey = 'ID_TIPO_SERVICIO';
  public $timestamps = false;
}
