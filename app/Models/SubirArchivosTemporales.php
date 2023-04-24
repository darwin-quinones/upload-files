<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class SubirArchivosTemporales extends Model
{
  protected $table = 'subir_archivos_temporales_2';
  protected $primaryKey = 'ID_TABLA';
  public $timestamps = false;
}
