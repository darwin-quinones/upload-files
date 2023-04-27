<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ConceptosFacturacion extends Model
{
  protected $table = 'conceptos_facturacion_2';
  protected $primaryKey = 'ID_CONCEPTO_FACT';
  public $timestamps = false;
}
