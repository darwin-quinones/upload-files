<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class TarifaAire extends Model
{
  protected $table = 'tarifas_aire_2';
  protected $primaryKey = 'ID_TARIFA';
  public $timestamps = false;
}
