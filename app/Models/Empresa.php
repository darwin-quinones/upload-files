<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Empresa extends Model
{
  protected $table = 'empresas_2';
  protected $primaryKey = 'ID_EMPRESA';
  public $timestamps = false;
}
