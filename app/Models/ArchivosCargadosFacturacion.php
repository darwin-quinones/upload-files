<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ArchivosCargadosFacturacion extends Model
{
  protected $table = 'archivos_cargados_facturacion_2';
  protected $primaryKey = 'ID_TABLA';
  public $timestamps = false;
}
