<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Tarifa extends Model
{
  protected $table = 'tarifas_2';
  protected $primaryKey = 'ID_TARIFA';
  public $timestamps = false;
}
