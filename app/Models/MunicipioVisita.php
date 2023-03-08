<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MunicipioVisita extends Model
{
  protected $table = 'municipios_visitas_2';
  protected $primaryKey = 'ID_TABLA';
  public $timestamps = false;
}
