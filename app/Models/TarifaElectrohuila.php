<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class TarifaElectrohuila extends Model
{
  protected $table = 'tarifas_electrohuila_2';
  protected $primaryKey = 'ID_TARIFA';
  public $timestamps = false;
}
