<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use App\Models\File;
use App\Models\ArchivosCargadosCatastro;
use App\Models\ArchivosCargadosFacturacion;
use App\Models\ArchivosCargadosRecaudo;
use App\Models\ArchivosCargadosRefacturacion;
use App\Models\ArchivosCargadosCens;
use App\Models\ArchivosCargadosOYMRI;
use App\Models\FacturacionOYMRI;
use App\Models\DetalleFactComer;
use App\Models\FacturacionComercializadores;
use App\Models\ArchivosCargadosFactComer;
use App\Models\TipoMercado;
use App\Models\TipoSubMercado;
use App\Models\TipoPago;
use App\Models\TipoEdad;
use App\Models\Comercializador;
use App\Models\RecaudoEspecial;
use App\Models\ConceptosFacturacion;
use App\Models\Empresa;
use App\Models\Tarifa;
use App\Models\TarifaAire;
use App\Models\Corregimiento;
use App\Models\EstadoSuministro;
use App\Models\Municipio;
use App\Models\MunicipioVisita;
use App\Models\TarifaElectrohuila;
use App\Models\TipoCliente;
use App\Models\TipoClienteAire;
use App\Models\TipoClienteElectrohuila;
use App\Models\TipoConceptoAire;
use App\Models\TipoServicio;

// LIBRERIA PHP SPREADSHEET
require '../vendor/autoload.php';


class FileController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */


    public function excelReports()
    {
        return Inertia::render('ExcelReports');
    }
    public function genereExcelReports(Request $request)
    {
        $report_code = $request->input('report_code');
        switch ($report_code) {
                //? Reporte Operadores - Periodo
            case 5:
                // Reporte Cliente Especiales - Periodo
                $id_year = $request->input('id_year');
                $id_month = $request->input('id_month');
                $data = DB::select("SELECT * from facturacion_especiales_2");
                $estado = '';
                foreach ($data as &$row) {
                    $query_recaudo_especial  = RecaudoEspecial::where('ID_FACTURACION', $row->ID_FACTURACION)->first();
                    //echo $query_recaudo_especial->VALOR_RECAUDO;
                    if ($query_recaudo_especial) {
                        $new_valor_recaudo = array('VALOR_RECAUDO' => $query_recaudo_especial->VALOR_RECAUDO);
                        $cartera_a_la_fecha = ($row->VALOR_FACTURA - $query_recaudo_especial->VALOR_RECAUDO);
                        $new_cartera_a_la_fecha = array('CARTERA_A_LA_FECHA' => $cartera_a_la_fecha);
                        $new_fecha_pago_soporte = array('FECHA_PAGO_SOPORTE' => $query_recaudo_especial->FECHA_PAGO_SOPORTE);
                        $new_fecha_pago_bitacora = array('FECHA_PAGO_BITACORA' => $query_recaudo_especial->FECHA_PAGO_BITACORA);
                        switch ($query_recaudo_especial->ESTADO_RECAUDO) {
                            case '1':
                                $estado = 'ENTREGADO';
                                break;
                        }
                        $new_estado_recaudo = array('ESTADO_RECAUDO' => $estado);
                        $new_observ_recaudo = array('OBSERV_RECADO' => $query_recaudo_especial->OBSERVACIONES);
                        $array_row = json_decode(json_encode($row), true);
                        array_splice($array_row, 18, 0, array($new_valor_recaudo));
                        array_splice($array_row, 19, 0, array($new_cartera_a_la_fecha));

                        array_push($array_row, $new_fecha_pago_soporte, $new_fecha_pago_bitacora, $new_estado_recaudo, $new_observ_recaudo);
                        $row = (object)$array_row; // Convert the array back to an object
                    } else {

                        // convert stdClass object to an array
                        $array_row = json_decode(json_encode($row), true);
                        $new_valor_recaudo = array('VALOR_RECAUDO' => 0);

                        $new_cartera_a_la_fecha = array('CARTERA_A_LA_FECHA' => $row->VALOR_FACTURA);
                        $new_fecha_pago_soporte = array('FECHA_PAGO_SOPORTE' => '');
                        $new_fecha_pago_bitacora = array('FECHA_PAGO_BITACORA' => '');
                        $new_estado_recaudo = array('ESTADO_RECAUDO' => '');
                        $new_observ_recaudo = array('OBSERV_RECADO' => '');
                        // Add the new element at position 18
                        array_splice($array_row, 18, 0, array($new_valor_recaudo));
                        array_splice($array_row, 19, 0, array($new_cartera_a_la_fecha));
                        array_push($array_row, $new_fecha_pago_soporte, $new_fecha_pago_bitacora, $new_estado_recaudo, $new_observ_recaudo);
                        $row = (object)$array_row; // Convert the array back to an object
                    }
                }
                //var_dump($data);
                $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

                // delete the default active sheet
                $mySpreadsheet->removeSheetByIndex(0);

                // Create "Sheet 1" tab as the first worksheet.
                // https://phpspreadsheet.readthedocs.io/en/latest/topics/worksheets/adding-a-new-worksheet
                $worksheet1 = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "Reporte Operadores - Periodo");
                $mySpreadsheet->addSheet($worksheet1, 0);

                $dataArray = json_decode(json_encode($data), true);

                $worksheet1->fromArray($dataArray, null, 'A1');
                $worksheets = [$worksheet1];
                // Change the widths of the columns to be appropriately large for the content in them.
                foreach ($worksheets as $worksheet) {
                    foreach ($worksheet->getColumnIterator() as $column) {
                        $worksheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
                    }
                }
                $filename = "Reporte Cliente Especiales - Periodo " . $id_year . $id_month . ".xlsx";

                // set the path of thr directory where the file will be uploaded
                $directoryPath = public_path('uploads/reports');
                if(!file_exists($directoryPath)){
                    mkdir($directoryPath, 0755, true);
                }
                $filePath = $directoryPath . '/'. $filename;
                if(file_exists($filePath)){
                    unlink($filePath);
                }
                // save to file
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
                $writer->save($filePath);
                break;
        }
    }

    public function index()
    {
        $files = File::latest()->get();
        return Inertia::render('FileUploadLiquidaciones', compact('files'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */

    public function fileRegister2(Request $request)
    {
        if ($request->files) {
            $files = $request->files;
            $cod_operador_red = '10';
            $total_size = 0;
            foreach ($files as $archivo) {
                $filename = $archivo->getClientOriginalName();
                $tempFile = $archivo;
                $filepath = public_path('uploads/');
                $file = $filepath . $filename;

                switch ($cod_operador_red) {
                    case '10':
                        $query_ruta = ArchivosCargadosOYMRI::where('RUTA', $filename)->first();
                        if ($query_ruta) {
                            echo 'El archivos ya existe: ' . $filename;
                            // in this case continue 2 works to continue with the next iteration
                            continue 2;
                        }
                        //echo 'El archivo no existe: '. $filename;
                        break;
                }
                move_uploaded_file($tempFile, $file);

                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($file);
                $sheet_base = $spreadsheet->getSheet(0);
                $sheetData = $sheet_base->toArray();
                foreach ($sheetData as $lines) {
                    $new_array = implode($lines);
                    $new_array = str_replace(' ', '', $new_array);
                    $total_size += strlen($new_array);
                }
            }
            echo 'Total files size: ' . $total_size;
        }
    }

    public function uploadOYMRI(Request $request)
    {
        return Inertia::render('FileUploadOYMRI');
    }
    public function FileUploadOYMRI(Request $request)
    {
        if ($request->files) {
            $mensajes = array();
            $files = $request->files;
            $consultas = array();
            $elementos = array();
            $valores = array();
            $id_usuario = 1;
            foreach ($files as $archivo) {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $filename = $archivo->getClientOriginalName();
                $tempFile = $archivo;
                $filepath = public_path('uploads/');
                $file = $filepath . $filename;
                $fecha_creacion = date('Y-m-d');
                $mes_consolidado = 'Marzo';
                $ano_factura = '2023';

                if (file_exists($file)) {
                    unlink($file);
                }

                $query_ruta = ArchivosCargadosOYMRI::where('RUTA', $filename)->first();
                if ($query_ruta) {
                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                    continue;
                }
                move_uploaded_file($tempFile, $file);

                switch ($mes_consolidado) {
                    case "Enero":
                        $id_mes = 1;
                        break;
                    case "Febrero":
                        $id_mes = 2;
                        break;
                    case "Marzo":
                        $id_mes = 3;
                        break;
                    case "Abril":
                        $id_mes = 4;
                        break;
                    case "Mayo":
                        $id_mes = 5;
                        break;
                    case "Junio":
                        $id_mes = 6;
                        break;
                    case "Julio":
                        $id_mes = 7;
                        break;
                    case "Agosto":
                        $id_mes = 8;
                        break;
                    case "Septiembre":
                        $id_mes = 9;
                        break;
                    case "Octubre":
                        $id_mes = 10;
                        break;
                    case "Noviembre":
                        $id_mes = 11;
                        break;
                    case "Diciembre":
                        $id_mes = 12;
                        break;
                }


                // SAVE FILE
                $archivos_cargados_oymri = new ArchivosCargadosOYMRI();
                $archivos_cargados_oymri->ANO_FACTURA = $ano_factura;
                $archivos_cargados_oymri->PERIODO = $id_mes;
                $archivos_cargados_oymri->RUTA = $filename;
                $archivos_cargados_oymri->FECHA_CREACION = $fecha_creacion;
                $archivos_cargados_oymri->ID_USUARIO = $id_usuario;
                $archivos_cargados_oymri->save();

                $query_filename_oymri = ArchivosCargadosOYMRI::where('RUTA', $filename)->first();
                $id_tabla_ruta_oymri = $query_filename_oymri->ID_TABLA;
                $spreedsheet = $reader->load($file);
                $sheet_base = $spreedsheet->getSheet(0);
                $sheetData = $sheet_base->toArray();
                unset($sheetData[0]);

                $valor_bruto = 0;
                $valor_bruto_total = 0;
                $valor_subtotal_local = 0;
                $valor_subtotal_local_total = 0;
                $valor_impuesto_local = 0;
                $valor_impuesto_local_total = 0;
                $valor_neto_local = 0;
                $valor_neto_local_total = 0;
                $valor_desc_local = 0;
                $valor_desc_local_total = 0;
                $i = 0;
                foreach ($sheetData as $row) {
                    $no_factura = trim($row[2]);
                    $fecha_factura = trim($row[3]);
                    $estado_factura = trim(strtoupper($row[7]));
                    $nombre_cliente = trim($row[8]);
                    $nombre_municipio = (trim(strtoupper($row[9])));
                    $query_municipio = MunicipioVisita::where('NOMBRE', $nombre_municipio)->first();
                    $id_municipio = $query_municipio->ID_MUNICIPIO;
                    $id_departamento = $query_municipio->ID_DEPARTAMENTO;
                    $valor_bruto = trim(str_replace(array('-', ','), '', $row[10]));
                    if ($valor_bruto == '') $valor_bruto = 0;
                    $valor_bruto_total = $valor_bruto_total + $valor_bruto;
                    $valor_desc_local = trim(str_replace(array('-', ','), '', $row[11]));

                    // ANOTHER CONDITIONAL
                    if ($valor_desc_local == '') $valor_desc_local = 0;
                    $valor_desc_local_total = $valor_desc_local_total + $valor_desc_local;
                    $valor_subtotal_local = trim(str_replace(array('-', ','), '', $row[12]));
                    if ($valor_subtotal_local == '') $valor_subtotal_local = 0;
                    $valor_subtotal_local_total = $valor_subtotal_local_total + $valor_subtotal_local;
                    $valor_impuesto_local =  trim(str_replace(array('-', ','), '', $row[13]));
                    if ($valor_impuesto_local == '') $valor_impuesto_local = 0;
                    //echo 'posicion: ' . $i;
                    $valor_impuesto_local_total = $valor_impuesto_local_total + $valor_impuesto_local;
                    $valor_neto_local = trim(str_replace(',', '', $row[14]));
                    if ($valor_neto_local == '') $valor_neto_local = 0;
                    $valor_neto_local_total = $valor_neto_local_total + $valor_neto_local;
                    $concepto = trim($row[15]);
                    $query_concepto = ConceptosFacturacion::where('NOMBRE', $concepto)->first();
                    if ($query_concepto) {
                        $id_concepto = $query_concepto->ID_CONCEPTO_FACT;
                    } else {
                        // INSERT
                        $concepto_facturacion = new ConceptosFacturacion();
                        $concepto_facturacion->NOMBRE = $concepto;
                        $concepto_facturacion->save();
                        $new_query_concepto = ConceptosFacturacion::where('NOMBRE', $concepto)->first();
                        $id_concepto = $new_query_concepto->ID_CONCEPTO_FACT;
                        $elementos[] = ['mensaje' => "Concepto facturación agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $concepto];
                    }

                    $periodo = trim($row[16]);
                    $mes_factura = trim($row[17]);
                    $ano_factura = trim($row[18]);

                    $empresa = trim(strtoupper($row[19]));
                    $query_empresa = Empresa::where('NOMBRE', $empresa)->first();
                    $id_empresa = $query_empresa->ID_EMPRESA;
                    $acta_mes = trim(strtoupper($row[20]));
                    $observaciones = trim($row[21]);

                    $observaciones == '' ? ' ' : $observaciones = $observaciones;

                    $values = array(
                        'NO_FACTURA' => $no_factura,
                        'FECHA_FACTURA' => $fecha_factura,
                        'ESTADO_FACTURA' => $estado_factura,
                        'NOMBRE_CLIENTE' => $nombre_cliente,
                        'ID_COD_DPTO' => $id_departamento,
                        'ID_COD_MPIO' => $id_municipio,
                        'VALOR_BRUTO' => $valor_bruto,
                        'VALOR_DESC_LOCAL' => $valor_desc_local,
                        'VALOR_SUBTOTAL_LOCAL' => $valor_subtotal_local,
                        'VALOR_IMP_LOCAL' => $valor_impuesto_local,
                        'VALOR_NETO_LOCAL' => $valor_neto_local,
                        'ID_CONCEPTO' => $id_concepto,
                        'PERIODO' => $periodo,
                        'MES_FACTURA' => $mes_factura,
                        'ANO_FACTURA' => $ano_factura,
                        'ID_EMPRESA' => $id_empresa,
                        'ACTA_MES' => $acta_mes,
                        'OBSERVACIONES' => $observaciones,
                        'ID_TABLA_RUTA' => $id_tabla_ruta_oymri,
                        'FECHA_CREACION' => $fecha_creacion,
                        'ID_USUARIO' => $id_usuario,
                    );
                    FacturacionOYMRI::insert($values);

                    $i++;
                }
                // ENF FOREACH ROW

                // QUERIES
                $consultas[] = DB::table('facturacion_oymri_2021_2')->select([
                    DB::raw('COUNT(*) AS TOTAL'),
                    DB::raw('SUM(VALOR_BRUTO) AS TOTAL_VALOR_BRUTO'),
                    DB::raw('SUM(VALOR_DESC_LOCAL) AS TOTAL_VALOR_DESC_LOCAL'),
                    DB::raw('SUM(VALOR_SUBTOTAL_LOCAL) AS TOTAL_VALOR_SUBTOTAL_LOCAL'),
                    DB::raw('SUM(VALOR_IMP_LOCAL) AS TOTAL_VALOR_IMP_LOCAL'),
                    DB::raw('SUM(VALOR_NETO_LOCAL) AS TOTAL_VALOR_NETO_LOCAL')
                ])->where('ID_TABLA_RUTA', $id_tabla_ruta_oymri)->get();

                $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                $valores[] = [
                    'total' => $i, 'valor_bruto_total' => $valor_bruto_total,
                    'valor_desc_local_total' => $valor_desc_local_total, '
                            valor_subtotal_local_total' => $valor_subtotal_local_total,
                    'valor_impuesto_local_total' => $valor_impuesto_local_total,
                    'valor_neto_local_total' => $valor_neto_local_total
                ];

                unlink($file);
            }
            // END FOREACH FILES
            return ["resultado" => $consultas, "mensajes" => $mensajes, "elementos" => $elementos, "valores" => $valores];
        }
    }

    public function uploadComercializadores(Request $request)
    {
        return Inertia::render('FileUploadComercializadores');
    }
    public function FileUploadComercializadores(Request $request)
    {

        function clearSpecialCharacters($string)
        {
            return str_replace('?', 'Ñ', utf8_decode(strtoupper(trim(str_replace(array("”", "#", ".", "'", ";", "/", "\\", "`", '"', "'"), "", stripAccents($string))))));
        }


        function stripAccents($str)
        {
            return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
        }

        if ($request->files) {
            $mensajes = array();
            $files = $request->files;
            $consultas = array();
            $elementos = array();
            $valores = array();

            $id_usuario = 1;

            foreach ($files as $archivo) {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $filename = $archivo->getClientOriginalName();
                $tempFile = $archivo;
                $filepath = public_path('uploads/');
                $file = $filepath . $filename;
                $fecha_creacion = date('Y-m-d');
                if (file_exists($file)) {
                    unlink($file);
                }
                move_uploaded_file($tempFile, $file);


                $spreedsheet = $reader->load($file);
                $sheet_base = $spreedsheet->getSheet(0);
                $sheetData = $sheet_base->toArray();
                unset($sheetData[0]);
                // $nombre_comercializadora = 'VATIA S.A. E.S.P.';
                $nombre_comercializadora = 'VATIA S.A. E.S.P.';
                $query_comercializador = Comercializador::where('NOMBRE', $nombre_comercializadora)->first();
                $id_comercializador = $query_comercializador->ID_COMERCIALIZADOR;

                $archivos_cargados_fac_comer = new ArchivosCargadosFactComer();
                $archivos_cargados_fac_comer->ID_COMERCIALIZADOR = $id_comercializador;
                $archivos_cargados_fac_comer->RUTA = $filename;
                $archivos_cargados_fac_comer->FECHA_CREACION = $fecha_creacion;
                $archivos_cargados_fac_comer->ID_USUARIO = $id_usuario;
                $archivos_cargados_fac_comer->save();

                // GET LAST ID
                $id_tabla_ruta_fact_comer = ArchivosCargadosFactComer::max('ID_TABLA');

                $estado_factura = 2;
                $i = 0;
                foreach ($sheetData as $row) {
                    // ASIGNACION DE VARIABLES;
                    $periodo = trim($row[0]);
                    $municipio = trim(strtoupper($row[10]));

                    $query_municipio = MunicipioVisita::where("NOMBRE", $municipio)->first();

                    $id_municipio = $query_municipio->ID_MUNICIPIO;
                    $id_departamento = $query_municipio->ID_DEPARTAMENTO;


                    // VERIFY IF INFO ALREADY EXISTE IN DATABASE
                    $query_verify_info = DetalleFactComer::where('PERIODO', $periodo)
                        ->where('ID_COD_DPTO', $id_departamento)->where('ID_COD_MPIO', $id_municipio)
                        ->where('ID_COMERCIALIZADOR', $id_comercializador)->where('ID_TABLA_RUTA', '<>', $id_tabla_ruta_fact_comer)->first();
                    if ($query_verify_info) {
                        $mensajes[] = ["mensaje" => "El periodo '" . $periodo . "' ya existe para el municipio: '" . $municipio . "'..", "file" => $file];
                        continue;
                    }

                    $factura = trim($row[1]);
                    $cliente_id = trim($row[2]);
                    $factura_tipo_id = trim($row[3]);
                    $nombre_cliente = clearSpecialCharacters($row[4]);
                    $direccion_cliente = clearSpecialCharacters($row[5]);
                    $nombre_mercado = trim(strtoupper($row[6]));
                    $query_tipo_mercado = TipoMercado::where('NOMBRE', $nombre_mercado)->first();
                    if (empty($query_tipo_mercado)) {
                        $tipo_mercado = new TipoMercado();
                        $tipo_mercado->NOMBRE = $nombre_mercado;
                        $tipo_mercado->save();
                        $query_new_tipo_mercado = TipoMercado::where('NOMBRE', $nombre_mercado)->first();
                        $id_tipo_mercado = $query_new_tipo_mercado->ID_TIPO_MERCADO;
                        $elementos[] = ['mensaje' => "Tipo mercado agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $nombre_mercado];
                    } else {
                        $id_tipo_mercado = $query_tipo_mercado->ID_TIPO_MERCADO;
                    }

                    $nombre_sub_mercado = trim($row[7]);
                    $query_tipo_sub_mercado = TipoSubMercado::where('NOMBRE', $nombre_sub_mercado)->first();
                    if (empty($query_tipo_sub_mercado)) {
                        $sub_mercado = new TipoSubMercado();
                        $sub_mercado->NOMBRE = $nombre_sub_mercado;
                        $sub_mercado->save();
                        $query_new_tipo_sub_mercado = TipoSubMercado::where('NOMBRE', $nombre_sub_mercado)->first();
                        $id_tipo_sub_mercado = $query_new_tipo_sub_mercado->ID_TIPO_SUB_MERCADO;
                        $elementos[] = ['mensaje' => "Tipo submercado agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $nombre_sub_mercado];
                    } else {
                        $id_tipo_sub_mercado = $query_tipo_sub_mercado->ID_TIPO_SUB_MERCADO;
                    }

                    $estrato = trim($row[8]);
                    $estrato == '' ? $estrato = 0 : $estrato = $estrato;

                    $fecha_elaboracion_fact = trim($row[11]);
                    $fecha_limite1 = trim($row[12]);
                    $fecha_limite2 = trim($row[13]);
                    $valor_total_fact = trim($row[16]);
                    $valor_fact_conc = trim($row[17]);
                    $fecha_pago = trim($row[18]);
                    $valor_pago_total = trim($row[19]);
                    $valor_pago_conc = trim($row[20]);
                    $consumo = trim($row[21]);
                    $csm_rea_cobrable = trim($row[22]);
                    $csm_sin_con_valor = trim($row[23]);
                    $tipo_nota = trim($row[24]);
                    $valor_nota = trim($row[25]);
                    $fact_total_conc = trim($row[26]);
                    $nombre_tipo_pago = trim($row[27]);
                    $query_tipo_pago = TipoPago::where('NOMBRE', $nombre_tipo_pago)->first();
                    if (empty($query_tipo_pago)) {
                        $tipo_pago = new TipoPago();
                        $tipo_pago->NOMBRE = $nombre_tipo_pago;
                        $tipo_pago->save();
                        $query_new_tipo_pago = TipoPago::where('NOMBRE', $nombre_tipo_pago)->first();
                        $id_tipo_pago = $query_new_tipo_pago->ID_TIPO_PAGO;
                        $elementos[] = ['mensaje' => "Tipo pago agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $nombre_tipo_pago];
                    } else {
                        $id_tipo_pago = $query_tipo_pago->ID_TIPO_PAGO;
                    }
                    $cartera_recuperada = trim($row[28]);
                    $saldo_cartera = trim($row[29]);
                    $edad_cierre_mes_ciclo = trim($row[30]);
                    $query_tipo_edad = TipoEdad::where('NOMBRE', $edad_cierre_mes_ciclo)->first();
                    if (empty($query_tipo_edad)) {
                        $tipo_edad = new TipoEdad();
                        $tipo_edad->NOMBRE = $edad_cierre_mes_ciclo;
                        $tipo_edad->save();
                        $query_new_tipo_edad = TipoEdad::where('NOMBRE', $edad_cierre_mes_ciclo)->first();
                        $id_tipo_edad = $query_new_tipo_edad->ID_TIPO_EDAD;
                        $elementos[] = ['mensaje' => "Tipo edad agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $edad_cierre_mes_ciclo];
                    } else {
                        $id_tipo_edad = $query_tipo_edad->ID_TIPO_EDAD;
                    }

                    $facturado_cartera = trim($row[31]);

                    // AQUI SE PONE EL 02 QUEMADO COMO DIA DE FACTURADO
                    $fecha_factura = substr($periodo, 0, 4) . '-' . substr($periodo, 4, 2) . '-' . '02';

                    $values = array(
                        'PERIODO' => $periodo, 'FACTURA' => $factura, 'CLIENTE_ID' => $cliente_id,
                        'FACTURA_TIPO_ID' => $factura_tipo_id, 'NOMBRE_CLIENTE' => $nombre_cliente,
                        'DIRECCION_CLIENTE' => $direccion_cliente, 'ID_TIPO_MERCADO' => $id_tipo_mercado,
                        'ID_TIPO_SUB_MERCADO' => $id_tipo_sub_mercado, 'ESTRATO' => $estrato,
                        'ID_COD_DPTO' => $id_departamento, 'ID_COD_MPIO' => $id_municipio,
                        'FECHA_ELAB_FACT' => $fecha_elaboracion_fact, 'FECHA_LIMITE1' => $fecha_limite1,
                        'FECHA_LIMITE2' => $fecha_limite2, 'VALOR_TOT_FACT' => $valor_total_fact,
                        'VALOR_FACT_CONC' => $valor_fact_conc, 'FECHA_PAGO' => $fecha_pago,
                        'VALOR_PAGO_TOT' => $valor_pago_total, 'VALOR_PAGO_CONC' => $valor_pago_conc,
                        'CONSUMO' => $consumo, 'CSM_REA_COBRABLE' => $csm_rea_cobrable,
                        'CSM_SIN_CON_VALOR' => $csm_sin_con_valor, 'TIPO_NOTA' => $tipo_nota,
                        'VALOR_NOTA' => $valor_nota, 'FACT_TOTAL_CONC' => $fact_total_conc,
                        'ID_TIPO_PAGO' => $id_tipo_pago, 'CARTERA_RECUP' => $cartera_recuperada,
                        'SALDO_CARTERA' => $saldo_cartera, 'ID_TIPO_EDAD' => $id_tipo_edad,
                        'FACT_CARTERA' => $facturado_cartera, 'ID_COMERCIALIZADOR' => $id_comercializador,
                        'FECHA_FACTURA' => $fecha_factura, 'ESTADO_FACTURA' => $estado_factura,
                        'ID_TABLA_RUTA' => $id_tabla_ruta_fact_comer, 'FECHA_CREACION' => $fecha_creacion,
                        'ID_USUARIO' => $id_usuario
                    );
                    DB::table('detalle_fact_comer_2021_2')->insert($values);
                    $i++;
                } // FINAL FOREACH ROWS
                unlink($file);
                // QUERIES
                $consultas[] = DB::table('detalle_fact_comer_2021_2')
                    ->select([
                        DB::raw('COUNT(*) AS TOTAL'),
                    ])->where('ID_TABLA_RUTA', $id_tabla_ruta_fact_comer)->get();
                $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                $valores[] = [
                    'total' => $i,
                ];
                if ($i == 0) {
                    ArchivosCargadosFactComer::where('ID_TABLA', $id_tabla_ruta_fact_comer)->delete();
                }

                $periods = DB::table('detalle_fact_comer_2021_2')
                    ->selectRaw('PERIODO')->where('ID_TABLA_RUTA', $id_tabla_ruta_fact_comer)->groupBy('PERIODO')->get();

                foreach ($periods as $period) {
                    $results_period = DB::select(" SELECT DISTINCT(DFC.ID_COD_MPIO), MV.NOMBRE AS NOMBRE_MPIO,
                        DFC.ID_COD_DPTO, DV.NOMBRE AS NOMBRE_DPTO, DFC.ID_COMERCIALIZADOR,
                        DFC.ESTADO_FACTURA, DFC.PERIODO, DFC.FECHA_FACTURA,
                        DFC.FECHA_CREACION,
                        SUM(DFC.VALOR_FACT_CONC) AS VALOR_FACT,
                        SUM(DFC.VALOR_NOTA) AS AJUSTE_FACT,
                        SUM(DFC.VALOR_PAGO_CONC) AS VALOR_RECA,
                        SUM(DFC.CSM_SIN_CON_VALOR) AS VALOR_ENER,
                        SUM(DFC.VALOR_PAGO_CONC + DFC.CARTERA_RECUP) AS VALOR_FAV,
                        SUM(DFC.CONSUMO) AS CONSUMO, COUNT(DFC.ID_COD_MPIO) AS NO_USUARIOS
                        FROM detalle_fact_comer_2021_2 DFC, departamentos_visitas_2 DV, municipios_visitas_2 MV
                        WHERE DFC.ID_COD_DPTO = DV.ID_DEPARTAMENTO
                        AND DFC.ID_COD_MPIO = MV.ID_MUNICIPIO
                        AND DV.ID_DEPARTAMENTO = MV.ID_DEPARTAMENTO
                        AND DFC.ID_TABLA_RUTA = ?
                        AND DFC.PERIODO = ?
                        GROUP BY DFC.ID_COD_MPIO, DFC.ID_COMERCIALIZADOR, MV.NOMBRE, DFC.ID_COD_DPTO, DV.NOMBRE, DFC.ESTADO_FACTURA, DFC.PERIODO, DFC.FECHA_FACTURA,
                        DFC.FECHA_CREACION
                        HAVING COUNT(DFC.ID_COD_MPIO) >= 1
                        ", [$id_tabla_ruta_fact_comer, $period->PERIODO]);

                    // SAVE INFO IN

                    foreach ($results_period as $result) {
                        $result_values = array(
                            'ID_COMERCIALIZADOR' => $result->ID_COMERCIALIZADOR,
                            'ID_COD_DPTO' => $result->ID_COD_DPTO,
                            'ID_COD_MPIO' => $result->ID_COD_MPIO,
                            'FECHA_FACTURA' => $result->FECHA_FACTURA,
                            'PERIODO_FACTURA' => $result->PERIODO,
                            'VALOR_FACTURA' => $result->VALOR_FACT,
                            'AJUSTE_FACT' => $result->AJUSTE_FACT,
                            'VALOR_RECAUDO' => $result->VALOR_RECA,
                            'AJUSTE_RECA' => 0,
                            'VALOR_ENERGIA' => $result->VALOR_ENER,
                            'CUOTA_ENERGIA' => 0,
                            'OTROS_AJUSTES' => 0,
                            'VALOR_FAVOR' => $result->VALOR_FAV,
                            'CONSUMO' => $result->CONSUMO,
                            'NO_USUARIOS' => $result->NO_USUARIOS,
                            'ESTADO_FACTURA' => $result->ESTADO_FACTURA,
                            'FECHA_CREACION' => $result->FECHA_CREACION,
                            'FECHA_ACTUALIZACION' => '1900-01-01',
                            'ID_USUARIO' => $id_usuario
                        );

                        DB::table('facturacion_comercializadores_2')->insert($result_values);
                    }
                }
            } // FINAL FOREACH FILES


            return ["Status" => true, "resultado" => $consultas, "mensajes" => $mensajes, "elementos" => $elementos, "valores" => $valores];
        }
    }

    public function fileUploadLiquidaciones(Request $request)
    {


        function clearSpecialCharacters($string)
        {
            return str_replace('?', 'Ñ', utf8_decode(strtoupper(trim(str_replace(array("”", "#", ".", "'", ";", "/", "\\", "`", '"', "'"), "", stripAccents($string))))));
        }


        function stripAccents($str)
        {
            return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
        }
        function getFilesSize($files, $cod_operador_red)
        {
            $total_size = 0;
            foreach ($files as $archivo) {
                $filename = $archivo->getClientOriginalName();
                $tempFile = $archivo;
                $filepath = public_path('uploads/');
                $file = $filepath . $filename;

                switch ($cod_operador_red) {
                    case '10':
                        $query_ruta = ArchivosCargadosOYMRI::where('RUTA', $filename)->first();
                        if ($query_ruta) {
                            // echo 'El archivos ya existe: '. $filename;
                            // in this case continue 2 works to continue with the next iteration
                            continue 2;
                        }
                        //echo 'El archivo no existe: '. $filename;
                        break;
                }


                move_uploaded_file($tempFile, $file);

                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($file);
                $sheet_base = $spreadsheet->getSheet(0);
                $sheetData = $sheet_base->toArray();
                foreach ($sheetData as $lines) {
                    $new_array = implode($lines);
                    $new_array = str_replace(' ', '', $new_array);
                    $total_size += strlen($new_array);
                }
            }
            return $total_size;
        }


        if ($request->files) {
            $mensajes = array();
            $max_files = 1;

            // if(count($request->files) > $max_files){
            //     return $mensajes[] = ["mensaje" => "Solo puede subir un maximo de '". $max_files . "' archivos"];
            // }
            $k = 0;
            $files = $request->files;
            $cod_operador_red = '11';
            $consultas = array();
            $elementos = array();
            $valores = array();
            // AFINIA Y AIRE electrohuila
            // $mes_consolidado = 'Agosto';
            // $ano_factura = '2022';

            $id_usuario = 1;
            // CENS
            $mes_consolidado = 'Enero';
            $ano_factura = '2023';



            // TABLES OF QUERIES
            $table_catastro = "catastro_" . strtolower($mes_consolidado) . $ano_factura . "_2";
            $table_facturacion = "facturacion_" . strtolower($mes_consolidado) . $ano_factura . "_2";
            $table_recaudo = "recaudo_" . strtolower($mes_consolidado) . $ano_factura . "_2";
            $table_refacturacion = "refacturacion_" . strtolower($mes_consolidado) . $ano_factura . "_2";
            $table_fact_reca_cens = "fact_reca_cens_" . strtolower($mes_consolidado) . "_" . $ano_factura . "_2";

            switch ($cod_operador_red) {
                    // CASO OYM-RI
                case '10':

                    // CASO DE CENS
                case '9':
                    $operador_red = 'CENS';
                    foreach ($files as $archivo) {

                        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                        $filename = $archivo->getClientOriginalName();
                        $tempFile = $archivo;
                        $filepath = public_path('uploads/');
                        $file = $filepath . $filename;
                        $fecha_creacion = date('Y-m-d');
                        $id_tipo_poblacion = 1;

                        $departamento = 'NORTE DE SANTANDER';
                        $municipio = 'VILLA DEL ROSARIO';

                        // VERIFY IF THE FILE ALREADY EXISTS
                        $query_ruta = ArchivosCargadosCens::where('RUTA', '=', $filename)->first();
                        if ($query_ruta) {
                            $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                            continue;
                        }

                        // INSTANCES
                        $archivos_cargados_cens = new ArchivosCargadosCens();

                        move_uploaded_file($tempFile, $file);
                        switch ($mes_consolidado) {
                            case "Enero":
                                $id_mes = 1;
                                break;
                            case "Febrero":
                                $id_mes = 2;
                                break;
                            case "Marzo":
                                $id_mes = 3;
                                break;
                            case "Abril":
                                $id_mes = 4;
                                break;
                            case "Mayo":
                                $id_mes = 5;
                                break;
                            case "Junio":
                                $id_mes = 6;
                                break;
                            case "Julio":
                                $id_mes = 7;
                                break;
                            case "Agosto":
                                $id_mes = 8;
                                break;
                            case "Septiembre":
                                $id_mes = 9;
                                break;
                            case "Octubre":
                                $id_mes = 10;
                                break;
                            case "Noviembre":
                                $id_mes = 11;
                                break;
                            case "Diciembre":
                                $id_mes = 12;
                                break;
                        }

                        // SAVE FILE
                        $archivos_cargados_cens->ANO_FACTURA = $ano_factura;
                        $archivos_cargados_cens->ID_MES_FACTURA = $id_mes;
                        $archivos_cargados_cens->MES_FACTURA = strtoupper($mes_consolidado);
                        $archivos_cargados_cens->DEPARTAMENTO = strtoupper($departamento);
                        $archivos_cargados_cens->MUNICIPIO = strtoupper($municipio);
                        $archivos_cargados_cens->OPERADOR_RED = $operador_red;
                        $archivos_cargados_cens->RUTA = $filename;
                        $archivos_cargados_cens->FECHA_CREACION = $fecha_creacion;
                        $archivos_cargados_cens->ID_USUARIO = $id_usuario;
                        $archivos_cargados_cens->save();

                        $query_filename_cens = ArchivosCargadosCens::where('RUTA', '=', $filename)->first();
                        $id_tabla_ruta_cens = $query_filename_cens->ID_TABLA;

                        $spreadsheet = $reader->load($file);
                        $sheet_base = $spreadsheet->getSheet(0);
                        $sheetData = $sheet_base->toArray();
                        // DELITE 6 FIRST ROWS
                        for ($e = 0; $e < 6; $e++) {
                            unset($sheetData[$e]);
                        }
                        $i = 0;
                        $total_facturacion = 0;
                        $total_recaudo = 0;
                        $total_cartera = 0;
                        foreach ($sheetData as $row) {
                            $id_cliente = trim($row[0]);
                            $nombre_cliente = strtoupper(trim($row[1]));
                            $direccion_vivienda = strtoupper(trim($row[2]));
                            $facturacion =  (trim(str_replace(array('$', ','), '', $row[3])));
                            $total_facturacion = $total_facturacion + $facturacion;
                            $recaudo =  (trim(str_replace(array('$', ','), '', $row[4])));
                            $total_recaudo = $total_recaudo + $recaudo;
                            $cartera =  (trim(str_replace(array('$', ','), '', $row[5])));
                            $total_cartera = $total_cartera + $cartera;
                            $antiguedad = trim($row[6]);

                            $query_select_municipio = Municipio::where('NOMBRE', '=', $municipio)->first();
                            $id_cod_depto = $query_select_municipio->ID_DEPARTAMENTO;
                            $id_cod_mpio = $query_select_municipio->ID_MUNICIPIO;
                            // SAVE INFO
                            $values = array(
                                'ID_CLIENTE' => $id_cliente,
                                'NOMBRE_CLIENTE' => $nombre_cliente,
                                'DIRECCION_VIVIENDA' => $direccion_vivienda,
                                'ID_COD_DPTO' => $id_cod_depto,
                                'ID_COD_MPIO' => $id_cod_mpio,
                                'FACTURACION' => $facturacion,
                                'RECAUDO' => $recaudo,
                                'CARTERA' => $cartera,
                                'ANTIGUEDAD' => $antiguedad,
                                'ANO_PERIODO' => $ano_factura,
                                'MES_PERIODO' => $id_mes,
                                'ID_TABLA_RUTA' => $id_tabla_ruta_cens,
                                'FECHA_CREACION' => $fecha_creacion,
                                'ID_USUARIO' => $id_usuario,
                            );
                            DB::table($table_fact_reca_cens)->insert($values);
                            $i++;
                        }
                        // FINAL FOREACH
                        $consultas[] = DB::table($table_fact_reca_cens)
                            ->select([
                                DB::raw('COUNT(*) AS TOTAL'),
                                DB::raw('SUM(FACTURACION) AS TOTAL_FACTURACION'),
                                DB::raw('SUM(RECAUDO) AS TOTAL_RECAUDO'),
                                DB::raw('SUM(CARTERA) AS TOTAL_CARTERA'),
                            ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta_cens)->get();
                        $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                        $valores[] = ['total_facturacion' => $total_facturacion, 'total_recaudo' => $total_recaudo, 'total_cartera' => $total_cartera];

                        unlink($file);
                        $k++;
                    }
                    // FIN FOREACH FILES
                    return ["resultado" => $consultas, "mensajes" => $mensajes, "elementos" => $elementos, "valores" => $valores];
                    // FIN CASO DE CENS
                    break;
                case '8':
                    $operador_red = 'ELECTROHUILA';
                    foreach ($files as $archivo) {
                        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                        $filename = $archivo->getClientOriginalName();
                        $tempFile = $archivo;
                        $filepath = public_path('uploads/');
                        $file = $filepath . $filename;

                        $fecha_creacion = date('Y-m-d');
                        $mes_consolidado = 'Agosto';

                        $id_tipo_poblacion = 1;
                        $ano_factura = '2022';
                        $mes_factura = 'AGOSTO';
                        $departamento = 'HUILA';
                        $municipio = 'PITALITO';



                        $query_ruta = DB::table('archivos_cargados_catastro_2')->where('RUTA', '=', $filename)->first();
                        if ($query_ruta) {
                            $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                            break;
                        }
                        //INSTANCES
                        $archivos_catastro = new ArchivosCargadosCatastro();
                        $archivos_facturacion = new ArchivosCargadosFacturacion();
                        $archivos_recaudo = new ArchivosCargadosRecaudo();

                        move_uploaded_file($tempFile, $file);
                        switch ($mes_consolidado) {
                            case "Enero":
                                $id_mes = 1;
                                break;
                            case "Febrero":
                                $id_mes = 2;
                                break;
                            case "Marzo":
                                $id_mes = 3;
                                break;
                            case "Abril":
                                $id_mes = 4;
                                break;
                            case "Mayo":
                                $id_mes = 5;
                                break;
                            case "Junio":
                                $id_mes = 6;
                                break;
                            case "Julio":
                                $id_mes = 7;
                                break;
                            case "Agosto":
                                $id_mes = 8;
                                break;
                            case "Septiembre":
                                $id_mes = 9;
                                break;
                            case "Octubre":
                                $id_mes = 10;
                                break;
                            case "Noviembre":
                                $id_mes = 11;
                                break;
                            case "Diciembre":
                                $id_mes = 12;
                                break;
                        }

                        // SE GURADA EL ARCHIVO CATASTRO
                        $archivos_catastro->ANO_FACTURA = $ano_factura;
                        $archivos_catastro->ID_MES_FACTURA = $id_mes;
                        $archivos_catastro->MES_FACTURA = $mes_factura;
                        $archivos_catastro->DEPARTAMENTO = $departamento;
                        $archivos_catastro->MUNICIPIO = $municipio;
                        $archivos_catastro->OPERADOR_RED = $operador_red;
                        $archivos_catastro->RUTA = $filename;
                        $archivos_catastro->FECHA_CREACION = $fecha_creacion;
                        $archivos_catastro->ID_USUARIO = $id_usuario;
                        $archivos_catastro->save();
                        $query_filename_catastro = ArchivosCargadosCatastro::where('RUTA', '=', $filename)->first();
                        $id_tabla_ruta_catastro = $query_filename_catastro->ID_TABLA;

                        // SE GURADA EL ARCHIVO CAT
                        $archivos_facturacion->ANO_FACTURA = $ano_factura;
                        $archivos_facturacion->ID_MES_FACTURA = $id_mes;
                        $archivos_facturacion->MES_FACTURA = $mes_factura;
                        $archivos_facturacion->DEPARTAMENTO = $departamento;
                        $archivos_facturacion->MUNICIPIO = $municipio;
                        $archivos_facturacion->OPERADOR_RED = $operador_red;
                        $archivos_facturacion->RUTA = $filename;
                        $archivos_facturacion->FECHA_CREACION = $fecha_creacion;
                        $archivos_facturacion->ID_USUARIO = $id_usuario;
                        $archivos_facturacion->save();
                        $query_filename_facturacion = ArchivosCargadosFacturacion::where('RUTA', '=', $filename)->first();
                        $id_tabla_ruta_facturacion = $query_filename_facturacion->ID_TABLA;

                        // SE GURADA EL ARCHIVO CATASTRO
                        $archivos_recaudo->ANO_FACTURA = $ano_factura;
                        $archivos_recaudo->ID_MES_FACTURA = $id_mes;
                        $archivos_recaudo->MES_FACTURA = $mes_factura;
                        $archivos_recaudo->DEPARTAMENTO = $departamento;
                        $archivos_recaudo->MUNICIPIO = $municipio;
                        $archivos_recaudo->OPERADOR_RED = $operador_red;
                        $archivos_recaudo->RUTA = $filename;
                        $archivos_recaudo->FECHA_CREACION = $fecha_creacion;
                        $archivos_recaudo->ID_USUARIO = $id_usuario;
                        $archivos_recaudo->save();
                        $query_filename_recaudo = ArchivosCargadosRecaudo::where('RUTA', '=', $filename)->first();
                        $id_tabla_ruta_recaudo = $query_filename_recaudo->ID_TABLA;


                        $spreadsheet = $reader->load($file);
                        // $sheet_base = $spreadsheet->getSheetByName('BASE');
                        $sheet_base = $spreadsheet->getSheet(0);
                        $number_rows = $sheet_base->getHighestDataRow();
                        $sheetData = $sheet_base->toArray();
                        //SE ELIMINA LA PRIMERA FILA
                        unset($sheetData[0]);
                        $i = 0;
                        $total_deuda_corriente = 0;
                        $total_deuda_cuota = 0;
                        $total_importe_trans_reca = 0;
                        $total_importe_trans_fact = 0;
                        $total_valor_recibo = 0;
                        foreach ($sheetData as $row) {

                            $id_tipo_servicio = 1;
                            $nombre_tarifa = strtoupper(str_replace(" ", "_", trim($row[10]))); //ESTRATO_1
                            $query_tarifa = TarifaElectrohuila::where('NOMBRE', '=', $nombre_tarifa)->first();
                            if (empty($query_tarifa)) {
                                $tarifa_instance = new TarifaElectrohuila();
                                $tarifa_instance->NOMBRE = $nombre_tarifa;
                                $tarifa_instance->COD_TARIFA = '';
                                $tarifa_instance->save();
                                $elementos[] = ['mensaje' => "Tarifa agregada en la posición '" . $i . "' ", 'elemento_agregado' =>  $nombre_tarifa];
                            }
                            $query_tarifa = TarifaElectrohuila::where('NOMBRE', '=', $nombre_tarifa)->first();
                            $id_tarifa = trim($query_tarifa->ID_TARIFA);

                            $nic = trim($row[6]);
                            $nis = trim($row[6]);
                            $nombre_propietario = clearSpecialCharacters($row[7]);
                            $direccion_vivienda = clearSpecialCharacters($row[9]);
                            $consumo_facturado = trim(str_replace(",", ".", $row[16]));

                            $descripcion_mpio = strtoupper(trim($row[4]));
                            $posicion_gion = strrpos($descripcion_mpio, '-');
                            $nombre_mpio = substr($descripcion_mpio, $posicion_gion + 1); // PITALITO
                            // aqui consulto informacion del municipio
                            $query_municipio = MunicipioVisita::where('NOMBRE', '=', $nombre_mpio)->first();
                            $id_departamento = $query_municipio->ID_DEPARTAMENTO;
                            $id_municipio = $query_municipio->ID_MUNICIPIO;
                            $id_corregimiento = 0;

                            // PREGUNTAR AQUÍ AL ING
                            $deuda_corriente = trim(str_replace(",", ".", $row[26]));
                            $deuda_cuota = 0;
                            //$deuda_cuota = trim(str_replace(",", ".", $row[22]));
                            $id_estado_suministro = 0;
                            $total_deuda_corriente = $total_deuda_corriente + $deuda_corriente;
                            $total_deuda_cuota = $total_deuda_cuota + $deuda_cuota;

                            // REGISTRO EN LA TABLA DE CATASTRO
                            $catastro_values = array(
                                'ID_TIPO_SERVICIO' => $id_tipo_servicio,
                                'ID_TARIFA' => $id_tarifa,
                                'NIC' => $nic,
                                'NIS' => $nis,
                                'NOMBRE_PROPIETARIO' => $nombre_propietario,
                                'DIRECCION_VIVIENDA' => $direccion_vivienda,
                                'CONSUMO_FACTURADO' => $consumo_facturado,
                                'ID_COD_DPTO' => $id_departamento,
                                'ID_COD_MPIO' => $id_municipio,
                                'ID_COD_CORREG' => $id_corregimiento,
                                'DEUDA_CORRIENTE' => $deuda_corriente,
                                'DEUDA_CUOTA' => $deuda_cuota,
                                'ID_ESTADO_SUMINISTRO' => $id_estado_suministro,
                                'ANO_CATASTRO' => $ano_factura,
                                'MES_CATASTRO' => $id_mes,
                                'ID_TABLA_RUTA' => $id_tabla_ruta_catastro,
                                'FECHA_CREACION' => $fecha_creacion,
                                'ID_USUARIO' => $id_usuario,
                                'OPERADOR_RED' => $operador_red
                            );
                            DB::table($table_catastro)->insert($catastro_values);

                            // REGISTRO TABLA FACTURACION
                            $cod_oper_cont = 0;
                            $sec_nis = 0;
                            $sec_rec = 0;
                            $fecha_fact_lect = $row[1] . "-" . $row[2] . "-" . "20"; //
                            $fecha_proc_reg = $row[1] . "-" . $row[2] . "-" . "20";
                            // SE OBTIENE ID TIPO CLIENTE PARA CUANDO NO APLIQUE
                            $query_tipo_cliente = TipoClienteElectrohuila::where('NOMBRE', '=', 'NO APLICA')->first();
                            $id_tipo_cliente = $query_tipo_cliente->ID_TIPO_CLIENTE; // Poner en tabla - No aplica
                            $id_estado_contrato = 0;
                            $concepto = 0;
                            $importe_trans_fact = $row[18];
                            $total_importe_trans_fact = $total_importe_trans_fact + $importe_trans_fact;

                            $fecha_trans =  $row[1] . "-" . $row[2] . "-" . "20";
                            $valor_recibo = trim(str_replace(",", ".", $row[16]));
                            $total_valor_recibo = $total_valor_recibo + $valor_recibo;
                            $id_sector_dpto = 0; //rural = 1; urbano = 2
                            $id_cod_correg = 0;
                            $id_cod_depto = $id_departamento;
                            $id_cod_mpio = $id_municipio;

                            $simbolo_variable = 0;
                            $consumo_kwh = trim($row[14]);

                            $facturacion_values = array(
                                'FECHA_PROC_REG' => $fecha_proc_reg,
                                'COD_OPER_CONT' => $cod_oper_cont,
                                'NIC' => $nic,
                                'NIS' => $nis,
                                'SEC_NIS' => $sec_nis,
                                'SEC_REC' => $sec_rec,
                                'FECHA_FACT_LECT' => $fecha_fact_lect,
                                'ID_TIPO_CLIENTE' => $id_tipo_cliente,
                                'ID_TARIFA' => $id_tarifa,
                                'ID_ESTADO_CONTRATO' => $id_estado_contrato,
                                'CONCEPTO' => $concepto,
                                'IMPORTE_TRANS' => $importe_trans_fact,
                                'FECHA_TRANS' => $fecha_trans,
                                'VALOR_RECIBO' => $valor_recibo,
                                'ID_SECTOR_DPTO' => $id_sector_dpto,
                                'ID_COD_MPIO' => $id_cod_mpio,
                                'ID_COD_CORREG' => $id_cod_correg,
                                'ID_COD_DPTO' => $id_cod_depto,
                                'SIMBOLO_VARIABLE' => $simbolo_variable,
                                'CONSUMO_KWH' => $consumo_kwh,
                                'ID_TIPO_POBLACION' => $id_tipo_poblacion,
                                'ANO_FACTURA' => $ano_factura,
                                'MES_FACTURA' => $id_mes,
                                'ID_TABLA_RUTA' => $id_tabla_ruta_facturacion,
                                'FECHA_CREACION' => $fecha_creacion,
                                'ID_USUARIO' => $id_usuario,
                                'OPERADOR_RED' => $operador_red,
                            );
                            DB::table($table_facturacion)->insert($facturacion_values);

                            $importe_trans_reca = $row[22];
                            $total_importe_trans_reca = $total_importe_trans_reca + $importe_trans_reca;

                            $recaudo_values = array(
                                'FECHA_PROC_REG' => $fecha_proc_reg,
                                'COD_OPER_CONT' => $cod_oper_cont,
                                'NIC' => $nic,
                                'NIS' => $nis,
                                'SEC_NIS' => $sec_nis,
                                'SEC_REC' => $sec_rec,
                                'FECHA_FACT_LECT' => $fecha_fact_lect,
                                'ID_TIPO_CLIENTE' => $id_tipo_cliente,
                                'ID_TARIFA' => $id_tarifa,
                                'ID_ESTADO_CONTRATO' => $id_estado_contrato,
                                'CONCEPTO' => $concepto,
                                'IMPORTE_TRANS' => $importe_trans_reca,
                                'FECHA_TRANS' => $fecha_trans,
                                'VALOR_RECIBO' => $valor_recibo,
                                'ID_SECTOR_DPTO' => $id_sector_dpto,
                                'ID_COD_MPIO' => $id_cod_mpio,
                                'ID_COD_CORREG' => $id_cod_correg,
                                'ID_COD_DPTO' => $id_cod_depto,
                                'SIMBOLO_VARIABLE' => $simbolo_variable,
                                'ID_TIPO_POBLACION' => $id_tipo_poblacion,
                                'ANO_FACTURA' => $ano_factura,
                                'MES_FACTURA' => $id_mes,
                                'ID_TABLA_RUTA' => $id_tabla_ruta_recaudo,
                                'FECHA_CREACION' => $fecha_creacion,
                                'ID_USUARIO' => $id_usuario,
                                'OPERADOR_RED' => $operador_red,
                            );

                            DB::table($table_recaudo)->insert($recaudo_values);
                            $i++;
                        }
                        // FINAL FOREACH SHEETDATA

                        $consultas[] = DB::table($table_catastro)
                            ->select([
                                DB::raw('COUNT(*) AS TOTAL'),
                                DB::raw("SUM(DEUDA_CORRIENTE) AS DEUDA_CORRIENTE"),
                                DB::raw("SUM(DEUDA_CUOTA) AS DEUDA_CUOTA"),
                            ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta_catastro)->get();
                        $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                        $valores[] = ['total_deuda_corriente' => $total_deuda_corriente, 'total_deuda_cuota' => $total_deuda_cuota];

                        $consultas[] = DB::table($table_facturacion)
                            ->select([
                                DB::raw('COUNT(*) AS TOTAL'),
                                DB::raw('SUM(IMPORTE_TRANS) AS TOTAL_IMPORTE_TRANS'),
                                DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBIDO')
                            ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta_facturacion)->get();
                        $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                        $valores[] = ['total_importe_trans' => $total_importe_trans_fact, 'total_valor_recibo' => $total_valor_recibo];

                        $consultas[] = DB::table($table_recaudo)
                            ->select([
                                DB::raw('COUNT(*) AS TOTAL'),
                                DB::raw('SUM(IMPORTE_TRANS) AS TOTAL_IMPORTE_TRANS'),
                                DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBIDO')
                            ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta_recaudo)->get();
                        $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                        $valores[] = ['total_importe_trans' => $total_importe_trans_reca, 'total_valor_recibo' => $total_valor_recibo];

                        unlink($file);
                        $k++;
                    }
                    // FIN FOREACH FILES
                    return ["resultado" => $consultas, "mensajes" => $mensajes, "elementos" => $elementos, "valores" => $valores];
                    break;
                case '7':
                    $operador_red = 'AIR-E';

                    foreach ($files as $archivo) {
                        $filename = $archivo->getClientOriginalName();
                        $tempFile = $archivo;
                        $filepath = public_path('uploads/');
                        $file = $filepath . $filename;

                        $fecha_creacion = date('Y-m-d');
                        $id_tipo_poblacion = 1;
                        $mes_factura = 'AGOSTO';
                        $departamento = 'LA GUAJIRA';
                        $municipio = 'RIOHACHA';

                        $iniciales_archivo = substr($filename, 0, 4);
                        switch ($iniciales_archivo) {
                            case 'CATA':

                                $query_ruta = ArchivosCargadosCatastro::where('RUTA', '=', $filename)->first();

                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                    break;
                                }
                                $result = new ArchivosCargadosCatastro();
                                move_uploaded_file($tempFile, $file);

                                switch ($mes_consolidado) {
                                    case "Enero":
                                        $id_mes = 1;
                                        break;
                                    case "Febrero":
                                        $id_mes = 2;
                                        break;
                                    case "Marzo":
                                        $id_mes = 3;
                                        break;
                                    case "Abril":
                                        $id_mes = 4;
                                        break;
                                    case "Mayo":
                                        $id_mes = 5;
                                        break;
                                    case "Junio":
                                        $id_mes = 6;
                                        break;
                                    case "Julio":
                                        $id_mes = 7;
                                        break;
                                    case "Agosto":
                                        $id_mes = 8;
                                        break;
                                    case "Septiembre":
                                        $id_mes = 9;
                                        break;
                                    case "Octubre":
                                        $id_mes = 10;
                                        break;
                                    case "Noviembre":
                                        $id_mes = 11;
                                        break;
                                    case "Diciembre":
                                        $id_mes = 12;
                                        break;
                                }

                                // SE GURADA EL ARCHIVO EN LA TABLA DE ARCHIVOS
                                $result->ANO_FACTURA = $ano_factura;
                                $result->ID_MES_FACTURA = $id_mes;
                                $result->MES_FACTURA = $mes_factura;
                                $result->DEPARTAMENTO = $departamento;
                                $result->MUNICIPIO = $municipio;
                                $result->OPERADOR_RED = $operador_red;
                                $result->RUTA = $filename;
                                $result->FECHA_CREACION = $fecha_creacion;
                                $result->ID_USUARIO = $id_usuario;
                                $result->save();

                                $query_filename = ArchivosCargadosCatastro::where('RUTA', '=', $filename)->first();
                                $id_tabla_ruta = $query_filename->ID_TABLA;

                                $total_deuda_corriente = 0;
                                $total_deuda_cuota = 0;
                                $data = file($file);
                                $row = array();
                                $i = 0;

                                unset($data[0]);
                                foreach ($data as $lines) {


                                    $corregimiento = new Corregimiento();
                                    $suministro = new EstadoSuministro();

                                    $row[] = explode("|", $lines);
                                    // NOTA: ID_TIPO_SERVICIO QUEMADO
                                    $id_tipo_servicio = 1;
                                    $nombre_tarifa = strtoupper(str_replace(" ", "_", trim($row[$i][4])));

                                    $query_tarifa = TarifaAire::where('NOMBRE', '=', $nombre_tarifa)->first();
                                    if (empty($query_tarifa)) {
                                        $tarifa_instance = new TarifaAire();
                                        $tarifa_instance->NOMBRE = $nombre_tarifa;
                                        $tarifa_instance->COD_TARIFA = '';
                                        $tarifa_instance->save();
                                        $elementos[] = ['mensaje' => "Tarifa agregada en la posición '" . $i . "' ", 'elemento_agregado' =>  $nombre_tarifa];
                                    }
                                    $query_tarifa = TarifaAire::where('NOMBRE', '=', $nombre_tarifa)->first();

                                    switch ($nombre_tarifa) {
                                        case 'COMERCIAL':
                                        case 'OFICIAL':
                                        case 'INDUSTRIAL':
                                        case 'OFICIAL_ADSCRITOS_PROPIOS':
                                            $id_tarifa = 0;
                                            break;
                                        default:
                                            // SI INCLUYE ESTRATOS
                                            $id_tarifa = trim($query_tarifa->ID_TARIFA);
                                            break;
                                    }

                                    $nic = trim($row[$i][6]);
                                    $nis = trim($row[$i][7]);
                                    $nombre_propietario = str_replace(array("”", "#", ".", "'", ";", "/", "\\", "`", '"', "'"), "", stripAccents($row[$i][8]));
                                    $direccion_vivienda = str_replace(array("”", "#", ".", "'", ";", "/", "\\", "`", '"', "'"), "", stripAccents($row[$i][9]));
                                    $consumo_facturado = trim(str_replace(",", ".", $row[$i][10]));

                                    $nombre_municipio =   strtoupper(str_replace("_", " ", utf8_decode(trim($row[$i][11]))));

                                    $query_municipio = Municipio::where('NOMBRE', '=', $nombre_municipio)->first();
                                    $id_departamento = $query_municipio->ID_DEPARTAMENTO;
                                    $id_municipio = $query_municipio->ID_MUNICIPIO;

                                    $nombre_corregimiento = strtoupper(trim(utf8_encode($row[$i][12])));

                                    // HAY VECES QUE EL CAMPO DE CORREGIMIENTO VIENE VACIO
                                    if ($nombre_corregimiento == '') {
                                        $query_corregimeinto_sin_nombre = Corregimiento::where('ID_DEPARTAMENTO', '=', $id_departamento)
                                            ->where('ID_MUNICIPIO', '=', $id_municipio)->where('NOMBRE', '=', 'SIN_NOMBRE')->first();
                                        $id_corregimiento = $query_corregimeinto_sin_nombre->ID_TABLA;
                                    } else {
                                        $query_corregimiento = Corregimiento::where('ID_DEPARTAMENTO', '=', $id_departamento)->where('ID_MUNICIPIO', '=', $id_municipio)->where('NOMBRE', '=', $nombre_corregimiento)->first();

                                        // SI NO ENCUENTRA CORREGIMIENTO LO AGREGAMOS
                                        if ($query_corregimiento) {
                                            $id_corregimiento = $query_corregimiento->ID_TABLA;
                                        } else {
                                            $query_max_id_corregimiento = Corregimiento::where('ID_DEPARTAMENTO', '=', $id_departamento)->where('ID_MUNICIPIO', '=', $id_municipio)->max('ID_CORREGIMIENTO');
                                            $id_max_corregimiento = $query_max_id_corregimiento + 1;

                                            // GUARDO UN NUEVO CORREGIMIENTO
                                            $corregimiento->ID_DEPARTAMENTO = $id_departamento;
                                            $corregimiento->ID_MUNICIPIO = $id_municipio;
                                            $corregimiento->ID_CORREGIMIENTO = $id_max_corregimiento;
                                            $corregimiento->NOMBRE = $nombre_corregimiento;
                                            $corregimiento->save();

                                            // CONSULTAR EL NUEVO CORREGIMIENTO
                                            $query_new_id_corregimiento = Corregimiento::max('ID_TABLA');
                                            $id_corregimiento = $query_new_id_corregimiento;
                                            $elementos[] = ['mensaje' => "Corregimiento agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $nombre_corregimiento];
                                            // NOTA: FALTA RETORNAR CORREGIMIENTO AGREGADO
                                        }
                                    }


                                    $deuda_corriente = trim(str_replace(",", ".", $row[$i][13]));
                                    $deuda_cuota = trim(str_replace(",", ".", $row[$i][14]));

                                    $estado_suministro = strtoupper(stripAccents(trim(utf8_encode($row[$i][15]))));
                                    $query_estado_suministro = EstadoSuministro::where('NOMBRE', '=', $estado_suministro)->first();
                                    if ($query_estado_suministro) {
                                        $id_estado_suministro = $query_estado_suministro->ID_ESTADO_SUMINISTRO;
                                    } else {
                                        // SE GUARDA UN NUEVO ESTADO SUMINISTRO
                                        $suministro->NOMBRE = $estado_suministro;
                                        $suministro->save();

                                        $query_new_estado_suministro = EstadoSuministro::where('NOMBRE', '=', $estado_suministro)->first();
                                        $id_estado_suministro = $query_new_estado_suministro->ID_ESTADO_SUMINISTRO;
                                        $elementos[] = ['mensaje' => "Estado suministro agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $estado_suministro];
                                    }

                                    $total_deuda_corriente = $total_deuda_corriente + $deuda_corriente;
                                    $total_deuda_cuota = $total_deuda_cuota + $deuda_cuota;

                                    $catastro_values = array(
                                        'ID_TIPO_SERVICIO' => $id_tipo_servicio,
                                        'ID_TARIFA' => $id_tarifa,
                                        'NIC' => $nic,
                                        'NIS' => $nis,
                                        'NOMBRE_PROPIETARIO' => $nombre_propietario,
                                        'DIRECCION_VIVIENDA' => $direccion_vivienda,
                                        'CONSUMO_FACTURADO' => $consumo_facturado,
                                        'ID_COD_DPTO' => $id_departamento,
                                        'ID_COD_MPIO' => $id_municipio,
                                        'ID_COD_CORREG' => $id_corregimiento,
                                        'DEUDA_CORRIENTE' => $deuda_corriente,
                                        'DEUDA_CUOTA' => $deuda_cuota,
                                        'ID_ESTADO_SUMINISTRO' => $id_estado_suministro,
                                        'ANO_CATASTRO' => $ano_factura,
                                        'MES_CATASTRO' => $id_mes,
                                        'ID_TABLA_RUTA' => $id_tabla_ruta,
                                        'FECHA_CREACION' => $fecha_creacion,
                                        'ID_USUARIO' => $id_usuario,
                                        'OPERADOR_RED' => $operador_red
                                    );

                                    DB::table($table_catastro)->insert($catastro_values);
                                    $i++;
                                }
                                // FIN FOREACH LINE
                                $consultas[] = DB::table($table_catastro)->select([
                                    DB::raw('COUNT(*) AS TOTAL'),
                                    DB::raw("SUM(DEUDA_CORRIENTE) AS DEUDA_CORRIENTE"),
                                    DB::raw("SUM(DEUDA_CUOTA) AS DEUDA_CUOTA"),
                                ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();

                                $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                                $valores[] = ['total_deuda_corriente' => $total_deuda_corriente, 'total_deuda_cuota' => $total_deuda_cuota];

                                unlink($file);
                                $k++;

                                break;
                            case 'FACT':

                                $query_ruta = ArchivosCargadosFacturacion::where('RUTA', '=', $filename)->first();
                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                    break;
                                }
                                $result = new ArchivosCargadosFacturacion();
                                move_uploaded_file($tempFile, $file);
                                switch ($mes_consolidado) {
                                    case "Enero":
                                        $id_mes = 1;
                                        break;
                                    case "Febrero":
                                        $id_mes = 2;
                                        break;
                                    case "Marzo":
                                        $id_mes = 3;
                                        break;
                                    case "Abril":
                                        $id_mes = 4;
                                        break;
                                    case "Mayo":
                                        $id_mes = 5;
                                        break;
                                    case "Junio":
                                        $id_mes = 6;
                                        break;
                                    case "Julio":
                                        $id_mes = 7;
                                        break;
                                    case "Agosto":
                                        $id_mes = 8;
                                        break;
                                    case "Septiembre":
                                        $id_mes = 9;
                                        break;
                                    case "Octubre":
                                        $id_mes = 10;
                                        break;
                                    case "Noviembre":
                                        $id_mes = 11;
                                        break;
                                    case "Diciembre":
                                        $id_mes = 12;
                                        break;
                                }

                                // SE GURADA EL ARCHIVO EN LA TABLA DE ARCHIVOS
                                $result->ANO_FACTURA = $ano_factura;
                                $result->ID_MES_FACTURA = $id_mes;
                                $result->MES_FACTURA = $mes_factura;
                                $result->DEPARTAMENTO = $departamento;
                                $result->MUNICIPIO = $municipio;
                                $result->OPERADOR_RED = $operador_red;
                                $result->RUTA = $filename;
                                $result->FECHA_CREACION = $fecha_creacion;
                                $result->ID_USUARIO = $id_usuario;
                                $result->save();

                                $query_filename = ArchivosCargadosFacturacion::where('RUTA', '=', $filename)->first();
                                $id_tabla_ruta = $query_filename->ID_TABLA;

                                $importe_trans = 0;
                                $total_valor_recibo = 0;
                                $total_importe_trans = 0;
                                $data = file($file);
                                $row = array();
                                $i = 0;
                                unset($data[0]);
                                foreach ($data as $lines) {

                                    $row[] = explode("|", $lines);
                                    $nombre_municipio = strtoupper(trim(utf8_encode($row[$i][1])));
                                    $query_municipio = Municipio::where('NOMBRE', '=', $nombre_municipio)->first();
                                    $id_cod_mpio = $query_municipio->ID_MUNICIPIO;
                                    $id_cod_depto = $query_municipio->ID_DEPARTAMENTO;

                                    $fecha_proc_reg = trim(substr($row[$i][11], 0, 4) . "-" . substr($row[$i][11], 4, 2) . "-" . substr($row[$i][11], 6, 2));
                                    $cod_oper_cont = trim($row[$i][6]);
                                    $nic = trim($row[$i][7]);
                                    $nis = trim($row[$i][8]);
                                    $sec_nis = trim($row[$i][9]);
                                    $sec_rec = trim($row[$i][10]);
                                    $fecha_fact_lect = trim(substr($row[$i][11], 0, 4) . "-" . substr($row[$i][11], 4, 2) . "-" . substr($row[$i][11], 6, 2));
                                    $nombre_tipo_cliente = strtoupper(trim(str_replace("-", "_", str_replace(" ", "_", $row[$i][12])))); //RESIDENCIAL
                                    $query_tipo_cliente = TipoClienteAire::where('NOMBRE', '=', $nombre_tipo_cliente)->first();
                                    if ($query_tipo_cliente) {
                                        $id_tipo_cliente = $query_tipo_cliente->ID_TIPO_CLIENTE;
                                    } else {
                                        $cliente = new TipoClienteAire();
                                        $cliente->NOMBRE = $nombre_tipo_cliente;
                                        $cliente->save();
                                        $query_tipo_cliente = TipoClienteAire::where('NOMBRE', '=', $nombre_tipo_cliente)->first();
                                        $id_tipo_cliente = $query_tipo_cliente->ID_TIPO_CLIENTE;
                                        $elementos[] = ['mensaje' => "Tipo cliente agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $nombre_tipo_cliente];
                                    }

                                    // EN FACT SOLO VOY A CONSULTAR Y SI NO GUARDO EN 0
                                    $nombre_tarifa = trim(strtoupper(str_replace(" ", "_", substr($row[$i][13], 12, 9)))); //ESTRATO_1
                                    switch (substr($nombre_tarifa, 0, 7)) {
                                            // SI INCLUYE ESTRATO BUSCA EL ID RELACIONADO O DE NO PONE 0 POR DEFECTO
                                        case 'ESTRATO':
                                            $query_tarifa = TarifaAire::where('NOMBRE', '=', $nombre_tarifa)->first();
                                            $id_tarifa = $query_tarifa->ID_TARIFA;
                                            break;
                                        default:
                                            $id_tarifa = 0;
                                            break;
                                    }

                                    $id_estado_contrato = trim($row[$i][14]);
                                    $importe_trans = trim(str_replace(",", ".", $row[$i][16]));
                                    $total_importe_trans = $total_importe_trans + $importe_trans;
                                    $fecha_trans = substr($row[$i][17], 0, 4) . "-" . substr($row[$i][17], 4, 2) . "-" . substr($row[$i][17], 6, 2);
                                    $valor_recibo = trim(str_replace(",", ".", $row[$i][18]));
                                    $total_valor_recibo = $total_valor_recibo + $valor_recibo;
                                    $id_sector_dpto = trim($row[$i][19]);

                                    $cod_concepto = trim(substr($row[$i][15], 0, 4)); // TOMO LOS 4 PRIMEROS VALORES
                                    $query_concepto = TipoConceptoAire::where('COD_CONCEPTO', '=', $cod_concepto)->first();
                                    if (empty($query_concepto)) {
                                        $nombre_concepto = trim(strtoupper(ucfirst(mb_substr($row[$i][15], 5, null, 'UTF-8'))));
                                        $nombre_concepto = str_replace("-", "_", str_replace(" ", "_", str_replace(".", "_", $nombre_concepto)));
                                        $concepto = new TipoClienteAire();
                                        $concepto->COD_CONCEPTO = $cod_concepto;
                                        $concepto->NOMBRE = $nombre_concepto;
                                        $concepto->save();
                                        $elementos[] = ['mensaje' => "Tipo concepto agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $nombre_concepto];
                                    }

                                    // SE CONSULTA CORREGIMIENTO POR EL NIC
                                    $query_corregimiento = DB::table($table_catastro)->where('NIC', '=', $nic)->first();
                                    if ($query_corregimiento) {
                                        $id_cod_correg = $query_corregimiento->ID_COD_CORREG;
                                    } else {
                                        $id_cod_correg = 0;
                                    }

                                    switch (trim($id_cod_mpio)) {
                                        case '286':
                                            $id_cod_mpio = 1;
                                            break;
                                        case '264':
                                            $id_cod_mpio = 1;
                                            break;
                                        default:
                                            $id_cod_mpio = trim($id_cod_mpio);
                                            break;
                                    }

                                    switch (trim($id_cod_depto)) {
                                        case '4':
                                            $id_cod_depto = 1;
                                            break;
                                        case '24':
                                            $id_cod_depto = 54;
                                            break;
                                        case '21':
                                            $id_cod_depto = 41;
                                            break;
                                        default:
                                            $id_cod_depto = trim($id_cod_depto);
                                            break;
                                    }

                                    switch ($id_cod_depto) {
                                        case '1':
                                        case '3':
                                        case '6':
                                        case '7':
                                        case '41':
                                        case '54':
                                            $simbolo_variable = trim($row[$i][23]);
                                            if ($query_corregimiento) {
                                                $consumo_kwh = trim($query_corregimiento->CONSUMO_FACTURADO);
                                            } else {
                                                $consumo_kwh = 0;
                                            }
                                            break;
                                        default:
                                            $simbolo_variable = 0;
                                            $consumo_kwh = 0;
                                            break;
                                    }

                                    $facturacion_values = array(
                                        'FECHA_PROC_REG' => $fecha_proc_reg,
                                        'COD_OPER_CONT' => $cod_oper_cont,
                                        'NIC' => $nic,
                                        'NIS' => $nis,
                                        'SEC_NIS' => $sec_nis,
                                        'SEC_REC' => $sec_rec,
                                        'FECHA_FACT_LECT' => $fecha_fact_lect,
                                        'ID_TIPO_CLIENTE' => $id_tipo_cliente,
                                        'ID_TARIFA' => $id_tarifa,
                                        'ID_ESTADO_CONTRATO' => $id_estado_contrato,
                                        'CONCEPTO' => $cod_concepto,
                                        'IMPORTE_TRANS' => $importe_trans,
                                        'FECHA_TRANS' => $fecha_trans,
                                        'VALOR_RECIBO'  => $valor_recibo,
                                        'ID_SECTOR_DPTO' => $id_sector_dpto,
                                        'ID_COD_MPIO' => $id_cod_mpio,
                                        'ID_COD_CORREG' => $id_cod_correg,
                                        'ID_COD_DPTO' => $id_cod_depto,
                                        'SIMBOLO_VARIABLE' => $simbolo_variable,
                                        'CONSUMO_KWH' => $consumo_kwh,
                                        'ID_TIPO_POBLACION' => $id_tipo_poblacion,
                                        'ANO_FACTURA' => $ano_factura,
                                        'MES_FACTURA' => $id_mes,
                                        'ID_TABLA_RUTA' => $id_tabla_ruta,
                                        'FECHA_CREACION' => $fecha_creacion,
                                        'ID_USUARIO' => $id_usuario,
                                        'OPERADOR_RED' => $operador_red
                                    );
                                    DB::table($table_facturacion)->insert($facturacion_values);
                                    $i++;
                                }
                                // FIN FOREACH LINE
                                $consultas[] = DB::table($table_facturacion)->select([
                                    DB::raw('COUNT(*) AS TOTAL'),
                                    DB::raw('SUM(IMPORTE_TRANS) AS TOTAL_IMPORTE_TRANS'),
                                    DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBIDO')
                                ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();

                                $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                                $valores[] = ['total_importe_trans' => $total_importe_trans, 'total_valor_recibo' => $total_valor_recibo];
                                unlink($file);
                                $k++;

                                break;
                            case 'RECA':
                                $query_ruta = ArchivosCargadosRecaudo::where('RUTA', '=', $filename)->first();
                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                    break;
                                }
                                $result = new ArchivosCargadosRecaudo();
                                move_uploaded_file($tempFile, $file);
                                switch ($mes_consolidado) {
                                    case "Enero":
                                        $id_mes = 1;
                                        break;
                                    case "Febrero":
                                        $id_mes = 2;
                                        break;
                                    case "Marzo":
                                        $id_mes = 3;
                                        break;
                                    case "Abril":
                                        $id_mes = 4;
                                        break;
                                    case "Mayo":
                                        $id_mes = 5;
                                        break;
                                    case "Junio":
                                        $id_mes = 6;
                                        break;
                                    case "Julio":
                                        $id_mes = 7;
                                        break;
                                    case "Agosto":
                                        $id_mes = 8;
                                        break;
                                    case "Septiembre":
                                        $id_mes = 9;
                                        break;
                                    case "Octubre":
                                        $id_mes = 10;
                                        break;
                                    case "Noviembre":
                                        $id_mes = 11;
                                        break;
                                    case "Diciembre":
                                        $id_mes = 12;
                                        break;
                                }

                                // SE GURADA EL ARCHIVO EN LA TABLA DE ARCHIVOS
                                $result->ANO_FACTURA = $ano_factura;
                                $result->ID_MES_FACTURA = $id_mes;
                                $result->MES_FACTURA = $mes_factura;
                                $result->DEPARTAMENTO = $departamento;
                                $result->MUNICIPIO = $municipio;
                                $result->OPERADOR_RED = $operador_red;
                                $result->RUTA = $filename;
                                $result->FECHA_CREACION = $fecha_creacion;
                                $result->ID_USUARIO = $id_usuario;
                                $result->save();

                                $query_filename = ArchivosCargadosRecaudo::where('RUTA', '=', $filename)->first();
                                $id_tabla_ruta = $query_filename->ID_TABLA;
                                $importe_trans = 0;
                                $total_valor_recibo = 0;
                                $total_importe_trans = 0;
                                $data = file($file);
                                $row = array();
                                $i = 0;
                                unset($data[0]);
                                foreach ($data as $lines) {

                                    $row[] = explode("|", $lines);

                                    $nombre_municipio =   strtoupper(str_replace("_", " ", trim(utf8_decode($row[$i][1]))));
                                    $query_municipio = Municipio::where('NOMBRE', '=', $nombre_municipio)->first();
                                    $id_cod_depto = $query_municipio->ID_DEPARTAMENTO;
                                    $id_cod_mpio = $query_municipio->ID_MUNICIPIO;

                                    $fecha_proc_reg = trim(substr($row[$i][11], 0, 4) . "-" . substr($row[$i][11], 4, 2) . "-" . substr($row[$i][11], 6, 2));
                                    $cod_oper_cont = strtoupper(trim($row[$i][6]));
                                    $nic = trim($row[$i][7]);
                                    $nis = trim($row[$i][8]);
                                    $sec_nis = trim($row[$i][9]);
                                    $sec_rec = trim($row[$i][10]);
                                    $fecha_fact_lect = substr($row[$i][11], 0, 4) . "-" . substr($row[$i][11], 4, 2) . "-" . substr($row[$i][11], 6, 2);

                                    $nombre_tipo_cliente = strtoupper(trim(str_replace("-", "_", str_replace(" ", "_", $row[$i][12]))));
                                    $query_tipo_cliente = TipoClienteAire::where('NOMBRE', '=', $nombre_tipo_cliente)->first();
                                    if ($query_tipo_cliente) {
                                        $id_tipo_cliente = $query_tipo_cliente->ID_TIPO_CLIENTE;
                                    } else {
                                        $tipo_cliente = new TipoClienteAire();
                                        $tipo_cliente->NOMBRE = $nombre_tipo_cliente;
                                        $tipo_cliente->save();
                                        $query_tipo_cliente = TipoClienteAire::where('NOMBRE', '=', $nombre_tipo_cliente)->first();
                                        $id_tipo_cliente = $query_tipo_cliente->ID_TIPO_CLIENTE;
                                        $elementos[] = ['mensaje' => "Tipo cliente agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $nombre_tipo_cliente];
                                    }
                                    $nombre_tarifa = trim(strtoupper(str_replace(" ", "_", substr($row[$i][13], 12, 9)))); //ESTRATO_1
                                    switch (substr($nombre_tarifa, 0, 7)) {
                                            // SI INCLUYE ESTRATO BUSCA EL ID RELACIONADO O DE NO PONE 0 POR DEFECTO
                                        case 'ESTRATO':
                                            $query_tarifa = TarifaAire::where('NOMBRE', '=', $nombre_tarifa)->first();
                                            $id_tarifa = $query_tarifa->ID_TARIFA;
                                            break;
                                        default:
                                            $id_tarifa = 0;
                                            break;
                                    }

                                    $id_estado_contrato = trim($row[$i][14]);
                                    $importe_trans = trim(str_replace(",", ".", $row[$i][16]));
                                    $total_importe_trans = $total_importe_trans + $importe_trans;
                                    $fecha_trans = substr($row[$i][17], 0, 4) . "-" . substr($row[$i][17], 4, 2) . "-" . substr($row[$i][17], 6, 2);
                                    $valor_recibo = trim(str_replace(",", ".", $row[$i][18]));
                                    $total_valor_recibo = $total_valor_recibo + $valor_recibo;
                                    $id_sector_dpto = trim($row[$i][19]);

                                    // CONSULTO SI EXISTE ESE CODIGO SI NO EXISTE LO INGRESO
                                    $cod_concepto = trim(substr($row[$i][15], 0, 4)); // TOMO LOS 4 PRIMEROS VALORES
                                    $query_concepto = TipoConceptoAire::where('COD_CONCEPTO', '=', $cod_concepto)->first();
                                    if (empty($query_concepto)) {
                                        $nombre_concepto = trim(strtoupper(ucfirst(mb_substr($row[$i][15], 5, null, 'UTF-8'))));
                                        $nombre_concepto = str_replace("-", "_", str_replace(" ", "_", str_replace(".", "_", $nombre_concepto)));
                                        $concepto = new TipoClienteAire();
                                        $concepto->COD_CONCEPTO = $cod_concepto;
                                        $concepto->NOMBRE = $nombre_concepto;
                                        $concepto->save();
                                        $elementos[] = ['mensaje' => "Tipo concepto agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $nombre_concepto];
                                    }

                                    // SE CONSULTA CORREGIMIENTO POR EL NIC
                                    $query_corregimiento = DB::table($table_catastro)->where('NIC', '=', $nic)->first();
                                    if ($query_corregimiento) {
                                        $id_cod_correg = $query_corregimiento->ID_COD_CORREG;
                                    } else {
                                        $id_cod_correg = 0;
                                    }

                                    switch (trim($id_cod_mpio)) { // MUNICIPIO
                                        case '286':
                                            $id_cod_mpio = 1;
                                            break;
                                        case '264':
                                            $id_cod_mpio = 1;
                                            break;
                                        default:
                                            $id_cod_mpio = trim($id_cod_mpio);
                                            break;
                                    }


                                    switch (trim($id_cod_depto)) {
                                        case '4':
                                            $id_cod_depto = 1;
                                            break;
                                        case '24':
                                            $id_cod_depto = 54;
                                            break;
                                        case '21':
                                            $id_cod_depto = 41;
                                            break;
                                        default:
                                            $id_cod_depto = trim($id_cod_depto);
                                            break;
                                    }
                                    switch ($id_cod_depto) {
                                        case '1':
                                        case '3':
                                        case '6':
                                        case '7':
                                        case '41':
                                        case '54':
                                            $simbolo_variable = trim($row[$i][23]);
                                            break;
                                        default:
                                            $simbolo_variable = 0;
                                            break;
                                    }
                                    $recaudo_values = array(
                                        'FECHA_PROC_REG' => $fecha_proc_reg,
                                        'COD_OPER_CONT' => $cod_oper_cont,
                                        'NIC' => $nic,
                                        'NIS' => $nis,
                                        'SEC_NIS' => $sec_nis,
                                        'SEC_REC' => $sec_rec,
                                        'FECHA_FACT_LECT' => $fecha_fact_lect,
                                        'ID_TIPO_CLIENTE' => $id_tipo_cliente,
                                        'ID_TARIFA' => $id_tarifa,
                                        'ID_ESTADO_CONTRATO' => $id_estado_contrato,
                                        'CONCEPTO' => $cod_concepto,
                                        'IMPORTE_TRANS' => $importe_trans,
                                        'FECHA_TRANS' => $fecha_trans,
                                        'VALOR_RECIBO' => $valor_recibo,
                                        'ID_SECTOR_DPTO' => $id_sector_dpto,
                                        'ID_COD_MPIO' => $id_cod_mpio,
                                        'ID_COD_CORREG' => $id_cod_correg,
                                        'ID_COD_DPTO' => $id_cod_depto,
                                        'SIMBOLO_VARIABLE' => $simbolo_variable,
                                        'ID_TIPO_POBLACION' => $id_tipo_poblacion,
                                        'ANO_FACTURA' => $ano_factura,
                                        'MES_FACTURA' => $id_mes,
                                        'ID_TABLA_RUTA' => $id_tabla_ruta,
                                        'FECHA_CREACION' => $fecha_creacion,
                                        'ID_USUARIO' => $id_usuario,
                                        'OPERADOR_RED' => $operador_red
                                    );
                                    DB::table($table_recaudo)->insert($recaudo_values);
                                    $i++;
                                }
                                $consultas[] = DB::table($table_recaudo)
                                    ->select([
                                        DB::raw('COUNT(*) AS TOTAL'),
                                        DB::raw('SUM(IMPORTE_TRANS) AS TOTAL_IMPORTE_TRANS'),
                                        DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBIDO')
                                    ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();
                                $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                                $valores[] = ['total_importe_trans' => $total_importe_trans, 'total_valor_recibo' => $total_valor_recibo];

                                unlink($file);
                                $k++;
                                break;
                            case 'REFA':

                                $query_ruta = ArchivosCargadosRefacturacion::where('RUTA', '=', $filename)->first();
                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                    break;
                                }
                                $result = new ArchivosCargadosRefacturacion();
                                move_uploaded_file($tempFile, $file);

                                switch ($mes_consolidado) {
                                    case "Enero":
                                        $id_mes = 1;
                                        break;
                                    case "Febrero":
                                        $id_mes = 2;
                                        break;
                                    case "Marzo":
                                        $id_mes = 3;
                                        break;
                                    case "Abril":
                                        $id_mes = 4;
                                        break;
                                    case "Mayo":
                                        $id_mes = 5;
                                        break;
                                    case "Junio":
                                        $id_mes = 6;
                                        break;
                                    case "Julio":
                                        $id_mes = 7;
                                        break;
                                    case "Agosto":
                                        $id_mes = 8;
                                        break;
                                    case "Septiembre":
                                        $id_mes = 9;
                                        break;
                                    case "Octubre":
                                        $id_mes = 10;
                                        break;
                                    case "Noviembre":
                                        $id_mes = 11;
                                        break;
                                    case "Diciembre":
                                        $id_mes = 12;
                                        break;
                                }
                                // SE GURADA EL ARCHIVO EN LA TABLA DE ARCHIVOS
                                $result->ANO_FACTURA = $ano_factura;
                                $result->ID_MES_FACTURA = $id_mes;
                                $result->MES_FACTURA = $mes_factura;
                                $result->DEPARTAMENTO = $departamento;
                                $result->MUNICIPIO = $municipio;
                                $result->OPERADOR_RED = $operador_red;
                                $result->RUTA = $filename;
                                $result->FECHA_CREACION = $fecha_creacion;
                                $result->ID_USUARIO = $id_usuario;
                                $result->save();

                                $query_filename = ArchivosCargadosRefacturacion::where('RUTA', '=', $filename)->first();
                                $id_tabla_ruta = $query_filename->ID_TABLA;

                                $importe_trans = 0;
                                $total_valor_recibo = 0;
                                $total_importe_trans = 0;
                                $data = file($file);
                                $row = array();
                                $i = 0;
                                unset($data[0]);
                                foreach ($data as $lines) {
                                    $row[] = explode("|", $lines);

                                    $nombre_municipio =   strtoupper(str_replace("_", " ", trim(utf8_decode($row[$i][1]))));
                                    $query_municipio = Municipio::where('NOMBRE', '=', $nombre_municipio)->first();
                                    $id_cod_depto = $query_municipio->ID_DEPARTAMENTO;
                                    $id_cod_mpio = $query_municipio->ID_MUNICIPIO;

                                    $fecha_proc_reg = trim(substr($row[$i][11], 0, 4) . "-" . substr($row[$i][11], 4, 2) . "-" . substr($row[$i][11], 6, 2));
                                    $cod_oper_cont = strtoupper(trim($row[$i][6]));
                                    $nic = trim($row[$i][7]);
                                    $nis = trim($row[$i][8]);
                                    $sec_nis = trim($row[$i][9]);
                                    $sec_rec = trim($row[$i][10]);

                                    $fecha_fact_lect = substr($row[$i][11], 0, 4) . "-" . substr($row[$i][11], 4, 2) . "-" . substr($row[$i][11], 6, 2);

                                    $nombre_tipo_cliente = strtoupper(trim(str_replace("-", "_", str_replace(" ", "_", $row[$i][12]))));
                                    $query_tipo_cliente = TipoClienteAire::where('NOMBRE', '=', $nombre_tipo_cliente)->first();
                                    if ($query_tipo_cliente) {
                                        $id_tipo_cliente = $query_tipo_cliente->ID_TIPO_CLIENTE;
                                    } else {
                                        $tipo_cliente = new TipoClienteAire();
                                        $tipo_cliente->NOMBRE = $nombre_tipo_cliente;
                                        $tipo_cliente->save();
                                        $query_tipo_cliente = TipoClienteAire::where('NOMBRE', '=', $nombre_tipo_cliente)->first();
                                        $id_tipo_cliente = $query_tipo_cliente->ID_TIPO_CLIENTE;
                                        $elementos[] = ['mensaje' => "Tipo cliente agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $nombre_tipo_cliente];
                                    }
                                    $nombre_tarifa = trim(strtoupper(str_replace(" ", "_", substr($row[$i][13], 12, 9)))); //ESTRATO_1
                                    switch (substr($nombre_tarifa, 0, 7)) {
                                            // SI INCLUYE ESTRATO BUSCA EL ID RELACIONADO O DE NO PONE 0 POR DEFECTO
                                        case 'ESTRATO':
                                            $query_tarifa = TarifaAire::where('NOMBRE', '=', $nombre_tarifa)->first();
                                            $id_tarifa = $query_tarifa->ID_TARIFA;
                                            break;
                                        default:
                                            $id_tarifa = 0;
                                            break;
                                    }

                                    $id_estado_contrato = trim($row[$i][14]);
                                    $importe_trans = trim(str_replace(",", ".", $row[$i][16]));
                                    $total_importe_trans = $total_importe_trans + $importe_trans;
                                    $fecha_trans = substr($row[$i][17], 0, 4) . "-" . substr($row[$i][17], 4, 2) . "-" . substr($row[$i][17], 6, 2);
                                    $valor_recibo = trim(str_replace(",", ".", $row[$i][18]));
                                    $total_valor_recibo = $total_valor_recibo + $valor_recibo;
                                    $id_sector_dpto = trim($row[$i][19]);

                                    // CONSULTO SI EXISTE ESE CODIGO SI NO EXISTE LO INGRESO
                                    $cod_concepto = trim(substr($row[$i][15], 0, 4)); // TOMO LOS 4 PRIMEROS VALORES
                                    $query_concepto = TipoConceptoAire::where('COD_CONCEPTO', '=', $cod_concepto)->first();
                                    if (empty($query_concepto)) {
                                        $nombre_concepto = trim(strtoupper(ucfirst(mb_substr($row[$i][15], 5, null, 'UTF-8'))));
                                        $nombre_concepto = str_replace("-", "_", str_replace(" ", "_", str_replace(".", "_", $nombre_concepto)));
                                        $concepto = new TipoClienteAire();
                                        $concepto->COD_CONCEPTO = $cod_concepto;
                                        $concepto->NOMBRE = $nombre_concepto;
                                        $concepto->save();
                                        $elementos[] = ['mensaje' => "Tipo concepto agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $nombre_concepto];
                                    }

                                    // SE CONSULTA CORREGIMIENTO POR EL NIC
                                    // $query_corregimiento = CatastroAgosto2022::where('NIC', '=', $nic)->first();
                                    $query_corregimiento =  DB::table($table_catastro)->where('NIC', '=', $nic)->first();
                                    if ($query_corregimiento) {
                                        $id_cod_correg = $query_corregimiento->ID_COD_CORREG;
                                    } else {
                                        $id_cod_correg = 0;
                                    }

                                    switch (trim($id_cod_mpio)) { // MUNICIPIO
                                        case '286':
                                            $id_cod_mpio = 1;
                                            break;
                                        case '264':
                                            $id_cod_mpio = 1;
                                            break;
                                        default:
                                            $id_cod_mpio = trim($id_cod_mpio);
                                            break;
                                    }
                                    switch (trim($id_cod_depto)) {
                                        case '4':
                                            $id_cod_depto = 1;
                                            break;
                                        case '24':
                                            $id_cod_depto = 54;
                                            break;
                                        case '21':
                                            $id_cod_depto = 41;
                                            break;
                                        default:
                                            $id_cod_depto = trim($id_cod_depto);
                                            break;
                                    }
                                    switch ($id_cod_depto) {
                                        case '1':
                                        case '3':
                                        case '6':
                                        case '7':
                                        case '41':
                                        case '54':
                                            $simbolo_variable = trim($row[$i][23]);
                                            break;
                                        default:
                                            $simbolo_variable = 0;
                                            break;
                                    }

                                    $refacturacion_values = array(
                                        'FECHA_PROC_REG' => $fecha_proc_reg,
                                        'COD_OPER_CONT' => $cod_oper_cont,
                                        'NIC' => $nic,
                                        'NIS' => $nis,
                                        'SEC_NIS' => $sec_nis,
                                        'SEC_REC' => $sec_rec,
                                        'FECHA_FACT_LECT' => $fecha_fact_lect,
                                        'ID_TIPO_CLIENTE' => $id_tipo_cliente,
                                        'ID_TARIFA' => $id_tarifa,
                                        'ID_ESTADO_CONTRATO' => $id_estado_contrato,
                                        'CONCEPTO' => $cod_concepto,
                                        'IMPORTE_TRANS' => $importe_trans,
                                        'FECHA_TRANS' => $fecha_trans,
                                        'VALOR_RECIBO' => $valor_recibo,
                                        'ID_SECTOR_DPTO' => $id_sector_dpto,
                                        'ID_COD_MPIO' => $id_cod_mpio,
                                        'ID_COD_CORREG' => $id_cod_correg,
                                        'ID_COD_DPTO' => $id_cod_depto,
                                        'SIMBOLO_VARIABLE' => $simbolo_variable,
                                        'ID_TIPO_POBLACION' => $id_tipo_poblacion,
                                        'ANO_FACTURA' => $ano_factura,
                                        'MES_FACTURA' => $id_mes,
                                        'ID_TABLA_RUTA' => $id_tabla_ruta,
                                        'FECHA_CREACION' => $fecha_creacion,
                                        'ID_USUARIO' => $id_usuario,
                                        'OPERADOR_RED' => $operador_red,
                                    );
                                    DB::table($table_refacturacion)->insert($refacturacion_values);
                                    $i++;
                                }
                                // FIN FOREACH LINE
                                $consultas[] = DB::table($table_refacturacion)
                                    ->select([
                                        DB::raw('COUNT(*) AS TOTAL'),
                                        DB::raw('SUM(IMPORTE_TRANS) AS TOTAL_IMPORTE_TRANS'),
                                        DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBO')
                                    ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();
                                $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                                $valores[] = ['total_importe_trans' => $total_importe_trans, 'total_valor_recibo' => $total_valor_recibo];
                                unlink($file);
                                $k++;
                                break;
                        }
                        // FIN INICIALES ARCHIVOS
                    }
                    // FIN FOREACH FILES
                    return ["resultado" => $consultas, "mensajes" => $mensajes, "elementos" => $elementos, 'valores' => $valores];
                    break;
                case '6':
                    $operador_red = 'AFINIA';
                    foreach ($files as $archivo) {
                        // get the original file name
                        $filename = $archivo->getClientOriginalName();
                        $tempFile = $archivo;
                        $filepath = public_path('uploads/');
                        $file = $filepath . $filename;

                        $fecha_creacion = date('Y-m-d');
                        $mes_consolidado = 'Agosto';

                        $id_tipo_poblacion = 1;
                        $ano_factura = '2022';
                        $mes_factura = 'AGOSTO';
                        $departamento = 'BOLIVAR';
                        $municipio = 'ARJONA';

                        $iniciales_archivo = substr($filename, 0, 4);

                        switch ($iniciales_archivo) {
                            case 'CATA':
                                $query_ruta = ArchivosCargadosCatastro::where('RUTA', '=', $filename)->first();

                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                    break;
                                }
                                // INSTANCES
                                $result = new ArchivosCargadosCatastro();
                                //upload file in public directory
                                move_uploaded_file($tempFile, $file);
                                //array_push( "El archivo ya existe", $file);

                                switch ($mes_consolidado) {
                                    case "Enero":
                                        $id_mes = 1;
                                        break;
                                    case "Febrero":
                                        $id_mes = 2;
                                        break;
                                    case "Marzo":
                                        $id_mes = 3;
                                        break;
                                    case "Abril":
                                        $id_mes = 4;
                                        break;
                                    case "Mayo":
                                        $id_mes = 5;
                                        break;
                                    case "Junio":
                                        $id_mes = 6;
                                        break;
                                    case "Julio":
                                        $id_mes = 7;
                                        break;
                                    case "Agosto":
                                        $id_mes = 8;
                                        break;
                                    case "Septiembre":
                                        $id_mes = 9;
                                        break;
                                    case "Octubre":
                                        $id_mes = 10;
                                        break;
                                    case "Noviembre":
                                        $id_mes = 11;
                                        break;
                                    case "Diciembre":
                                        $id_mes = 12;
                                        break;
                                }

                                // SE GURADA EL ARCHIVO EN LA TABLA DE ARCHIVOS
                                $result->ANO_FACTURA = $ano_factura;
                                $result->ID_MES_FACTURA = $id_mes;
                                $result->MES_FACTURA = $mes_factura;
                                $result->DEPARTAMENTO = $departamento;
                                $result->MUNICIPIO = $municipio;
                                $result->OPERADOR_RED = $operador_red;
                                $result->RUTA = $filename;
                                $result->FECHA_CREACION = $fecha_creacion;
                                $result->ID_USUARIO = $id_usuario;
                                $result->save();

                                $query_filename = ArchivosCargadosCatastro::where('RUTA', '=', $filename)->first();
                                $id_tabla_ruta = $query_filename->ID_TABLA;
                                $total_deuda_corriente = 0;
                                $total_deuda_cuota = 0;
                                $data = file($file);
                                $row = array();
                                $i = 0;
                                //$query_max_id_corregimiento = Corregimiento::where('ID_DEPARTAMENTO', '=', 1)->where('ID_MUNICIPIO', '=', 4)->max('ID_CORREGIMIENTO');
                                foreach ($data as $lines) {

                                    // INSTANCES

                                    $corregimiento = new Corregimiento();
                                    $suministro = new EstadoSuministro();

                                    $row[] = explode('|', $lines);

                                    $nombre_tarifa = strtoupper(str_replace(" ", "_", trim($row[$i][3])));
                                    $query_tarifa = Tarifa::where('NOMBRE', '=', $nombre_tarifa)->select('ID_TARIFA')->first();
                                    $id_tarifa = $query_tarifa->ID_TARIFA;
                                    $nic = $row[$i][4];
                                    $nis = $row[$i][5];

                                    $nombre_propietario = clearSpecialCharacters($row[$i][6]);
                                    $direccion_vivienda = clearSpecialCharacters($row[$i][7]);


                                    $consumo_facturado = trim(str_replace(",", ".", $row[$i][8]));

                                    $nombre_municipio =   strtoupper(str_replace("_", " ", trim($row[$i][9])));
                                    $query_municipio = Municipio::where('NOMBRE', '=', $nombre_municipio)->first();
                                    $id_departamento = $query_municipio->ID_DEPARTAMENTO;
                                    $id_municipio = $query_municipio->ID_MUNICIPIO;

                                    $nombre_corregimiento = strtoupper(trim(utf8_encode($row[$i][10])));
                                    $query_corregimiento = DB::table('corregimientos_2')->where('ID_DEPARTAMENTO', '=', $id_departamento)->where('ID_MUNICIPIO', '=', $id_municipio)->where('NOMBRE', '=', $nombre_corregimiento)->first();

                                    // SI NO ENCUENTRA CORREGIMIENTO LO AGREGAMOS
                                    if ($query_corregimiento) {
                                        $id_corregimiento = $query_corregimiento->ID_TABLA;
                                    } else {
                                        $query_max_id_corregimiento = Corregimiento::where('ID_DEPARTAMENTO', '=', $id_departamento)->where('ID_MUNICIPIO', '=', $id_municipio)->max('ID_CORREGIMIENTO');
                                        $id_max_corregimiento = $query_max_id_corregimiento + 1;

                                        // GUARDO UN NUEVO CORREGIMIENTO
                                        $corregimiento->ID_DEPARTAMENTO = $id_departamento;
                                        $corregimiento->ID_MUNICIPIO = $id_municipio;
                                        $corregimiento->ID_CORREGIMIENTO = $id_max_corregimiento;
                                        $corregimiento->NOMBRE = $nombre_corregimiento;
                                        $corregimiento->save();

                                        // CONSULTAR EL NUEVO CORREGIMIENTO
                                        $query_new_id_corregimiento = Corregimiento::max('ID_TABLA');
                                        $id_corregimiento = $query_new_id_corregimiento;
                                        $elementos[] = ['mensaje' => "Corregimiento agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $nombre_corregimiento];
                                        // NOTA: FALTA RETORNAR CORREGIMIENTO AGREGADO
                                    }
                                    $deuda_corriente = trim(str_replace(",", ".", $row[$i][11]));
                                    $deuda_cuota = trim(str_replace(",", ".", $row[$i][12]));

                                    $estado_suministro = strtoupper(trim($row[$i][13]));
                                    $query_estado_suministro = EstadoSuministro::where('NOMBRE', '=', $estado_suministro)->first();
                                    if ($query_estado_suministro) {
                                        $id_estado_suministro = $query_estado_suministro->ID_ESTADO_SUMINISTRO;
                                    } else {
                                        // SE GUARDA UN NUEVO ESTADO SUMINISTRO
                                        $suministro->NOMBRE = $estado_suministro;
                                        $suministro->save();

                                        $query_new_estado_suministro = EstadoSuministro::where('NOMBRE', '=', $estado_suministro)->first();
                                        $id_estado_suministro = $query_new_estado_suministro->ID_ESTADO_SUMINISTRO;
                                        $elementos[] = ['mensaje' => "Estado suministro agregado en la posición '" . $i . "' ", 'elemento_agregado' =>  $estado_suministro];
                                    }


                                    $total_deuda_corriente = $total_deuda_corriente + $deuda_corriente;
                                    $total_deuda_cuota = $total_deuda_cuota + $deuda_cuota;

                                    $cod_tipo_servicio = trim(str_replace(array("”", "#", ".", "'", ";", "/", "\\", "`", '"'), "", stripAccents($row[$i][0])));

                                    //echo ' cod: '  .  $cod_tipo_servicio . ' pos: '. $i . ' direccion_vivienda: '. $direccion_vivienda;
                                    $query_tipo_servicio = TipoServicio::where('COD_TIPO_SERVICIO', '=', $cod_tipo_servicio)->first();
                                    $id_tipo_servicio = $query_tipo_servicio->ID_TIPO_SERVICIO;
                                    $catastro_values = array(
                                        'ID_TIPO_SERVICIO' => $id_tipo_servicio,
                                        'ID_TARIFA' => $id_tarifa,
                                        'NIC' => $nic,
                                        'NIS' => $nis,
                                        'NOMBRE_PROPIETARIO' => $nombre_propietario,
                                        'DIRECCION_VIVIENDA' => $direccion_vivienda,
                                        'CONSUMO_FACTURADO' => $consumo_facturado,
                                        'ID_COD_DPTO' => $id_departamento,
                                        'ID_COD_MPIO' => $id_municipio,
                                        'ID_COD_CORREG' => $id_corregimiento,
                                        'DEUDA_CORRIENTE' => $deuda_corriente,
                                        'DEUDA_CUOTA' => $deuda_cuota,
                                        'ID_ESTADO_SUMINISTRO' => $id_estado_suministro,
                                        'ANO_CATASTRO' => $ano_factura,
                                        'MES_CATASTRO' => $id_mes,
                                        'ID_TABLA_RUTA' => $id_tabla_ruta,
                                        'FECHA_CREACION' => $fecha_creacion,
                                        'ID_USUARIO' => $id_usuario,
                                        'OPERADOR_RED' => $operador_red,
                                    );

                                    DB::table($table_catastro)->insert($catastro_values);
                                    $i++;
                                }
                                // FIN FOREACH LINE

                                $consultas[] = DB::table($table_catastro)->select([
                                    DB::raw('COUNT(*) AS TOTAL'),
                                    DB::raw("SUM(DEUDA_CORRIENTE) AS DEUDA_CORRIENTE"),
                                    DB::raw("SUM(DEUDA_CUOTA) AS DEUDA_CUOTA"),
                                ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();
                                $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                                $valores[] = ['total_deuda_corriente' => $total_deuda_corriente, 'total_deuda_cuota' => $total_deuda_cuota];

                                unlink($file);
                                $k++;

                                break;
                            case 'FACT':

                                $query_ruta = ArchivosCargadosFacturacion::where('RUTA', '=', $filename)->first();

                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                    break;
                                }
                                $result = new ArchivosCargadosFacturacion();
                                move_uploaded_file($tempFile, $file);

                                switch ($mes_consolidado) {
                                    case "Enero":
                                        $id_mes = 1;
                                        break;
                                    case "Febrero":
                                        $id_mes = 2;
                                        break;
                                    case "Marzo":
                                        $id_mes = 3;
                                        break;
                                    case "Abril":
                                        $id_mes = 4;
                                        break;
                                    case "Mayo":
                                        $id_mes = 5;
                                        break;
                                    case "Junio":
                                        $id_mes = 6;
                                        break;
                                    case "Julio":
                                        $id_mes = 7;
                                        break;
                                    case "Agosto":
                                        $id_mes = 8;
                                        break;
                                    case "Septiembre":
                                        $id_mes = 9;
                                        break;
                                    case "Octubre":
                                        $id_mes = 10;
                                        break;
                                    case "Noviembre":
                                        $id_mes = 11;
                                        break;
                                    case "Diciembre":
                                        $id_mes = 12;
                                        break;
                                }

                                // SE GURADA EL ARCHIVO EN LA TABLA DE ARCHIVOS
                                $result->ANO_FACTURA = $ano_factura;
                                $result->ID_MES_FACTURA = $id_mes;
                                $result->MES_FACTURA = $mes_factura;
                                $result->DEPARTAMENTO = $departamento;
                                $result->MUNICIPIO = $municipio;
                                $result->OPERADOR_RED = $operador_red;
                                $result->RUTA = $filename;
                                $result->FECHA_CREACION = $fecha_creacion;
                                $result->ID_USUARIO = $id_usuario;
                                $result->save();

                                $query_filename = ArchivosCargadosFacturacion::where('RUTA', '=', $filename)->first();
                                $id_tabla_ruta = $query_filename->ID_TABLA;

                                $importe_trans = 0;
                                $total_importe_trans = 0;
                                $total_valor_recibo = 0;
                                $data = file($file);
                                $row = array();
                                $i = 0;

                                foreach ($data as $lines) {

                                    $row[] = explode("\t", $lines);
                                    $fecha_proc_reg = trim(substr($row[$i][0], 0, 4) . "-" . substr($row[$i][0], 4, 2) . "-" . substr($row[$i][0], 6, 2));
                                    $cod_oper_cont = trim($row[$i][1]);
                                    $nic = trim($row[$i][2]);
                                    $nis = trim($row[$i][3]);
                                    $sec_nis = trim($row[$i][4]);
                                    $sec_rec = trim($row[$i][5]);
                                    $fecha_fact_lect = substr($row[$i][6], 0, 4) . "-" . substr($row[$i][6], 4, 2) . "-" . substr($row[$i][6], 6, 2);

                                    $nombre_cliente = strtoupper(trim($row[$i][7]));
                                    $query_tipo_cliente = TipoCliente::where('NOMBRE', '=', $nombre_cliente)->first();
                                    $id_tipo_cliente = $query_tipo_cliente->ID_TIPO_CLIENTE;

                                    $nombre_tarifa = strtoupper(trim($row[$i][8]));
                                    $query_tarifa = Tarifa::where('NOMBRE', '=', $nombre_tarifa)->first();
                                    $id_tarifa = $query_tarifa->ID_TARIFA;

                                    $id_estado_contrato = trim($row[$i][9]);
                                    $concepto = trim($row[$i][10]);
                                    $importe_trans = trim(str_replace(",", ".", $row[$i][11]));
                                    $total_importe_trans = $total_importe_trans + $importe_trans;

                                    $fecha_trans = substr($row[$i][12], 0, 4) . "-" . substr($row[$i][12], 4, 2) . "-" . substr($row[$i][12], 6, 2);
                                    $valor_recibo = trim(str_replace(",", ".", $row[$i][13]));
                                    $total_valor_recibo = $total_valor_recibo + $row[$i][13];
                                    $id_sector_dpto = trim($row[$i][14]);

                                    $cod_mpio = trim($row[$i][15]);
                                    switch ($cod_mpio) {
                                        case '286':
                                            $id_cod_mpio = 1;
                                            break;
                                        case '264':
                                            $id_cod_mpio = 1;
                                            break;
                                        default:
                                            $id_cod_mpio = $cod_mpio;
                                            break;
                                    }
                                    $id_cod_correg = trim($row[$i][16]);
                                    $cod_depto = trim($row[$i][17]);
                                    switch ($cod_depto) {
                                        case '4':
                                            $id_cod_depto = 1;
                                            break;
                                        case '24':
                                            $id_cod_depto = 54;
                                            break;
                                        case '21':
                                            $id_cod_depto = 41;
                                            break;
                                        default:
                                            $id_cod_depto = trim($row[$i][17]);
                                            break;
                                    }

                                    switch ($id_cod_depto) {
                                        case '1':
                                        case '3':
                                        case '6':
                                        case '7':
                                        case '41':
                                        case '54':
                                            $simbolo_variable = trim($row[$i][18]);
                                            $consumo_kwh = trim($row[$i][19]);
                                            break;
                                        default:
                                            $simbolo_variable = 0;
                                            $consumo_kwh = 0;
                                            break;
                                    }
                                    $facturacion_values = array(
                                        'FECHA_PROC_REG' => $fecha_proc_reg,
                                        'COD_OPER_CONT' => $cod_oper_cont,
                                        'NIC' => $nic,
                                        'NIS' => $nis,
                                        'SEC_NIS' => $sec_nis,
                                        'SEC_REC' => $sec_rec,
                                        'FECHA_FACT_LECT' => $fecha_fact_lect,
                                        'ID_TIPO_CLIENTE' => $id_tipo_cliente,
                                        'ID_TARIFA' => $id_tarifa,
                                        'ID_ESTADO_CONTRATO' => $id_estado_contrato,
                                        'CONCEPTO' => $concepto,
                                        'IMPORTE_TRANS' => $importe_trans,
                                        'FECHA_TRANS' => $fecha_trans,
                                        'VALOR_RECIBO'  => $valor_recibo,
                                        'ID_SECTOR_DPTO' => $id_sector_dpto,
                                        'ID_COD_MPIO' => $id_cod_mpio,
                                        'ID_COD_CORREG' => $id_cod_correg,
                                        'ID_COD_DPTO' => $id_cod_depto,
                                        'SIMBOLO_VARIABLE' => $simbolo_variable,
                                        'CONSUMO_KWH' => $consumo_kwh,
                                        'ID_TIPO_POBLACION' => $id_tipo_poblacion,
                                        'ANO_FACTURA' => $ano_factura,
                                        'MES_FACTURA' => $id_mes,
                                        'ID_TABLA_RUTA' => $id_tabla_ruta,
                                        'FECHA_CREACION' => $fecha_creacion,
                                        'ID_USUARIO' => $id_usuario,
                                        'OPERADOR_RED' => $operador_red
                                    );
                                    DB::table($table_facturacion)->insert($facturacion_values);
                                    $i++;
                                }
                                // FIN FOREACH LINE
                                $consultas[] = DB::table($table_facturacion)
                                    ->select([
                                        DB::raw('COUNT(*) AS TOTAL'),
                                        DB::raw('SUM(IMPORTE_TRANS) AS TOTAL_IMPORTE_TRANS'),
                                        DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBIDO')
                                    ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();

                                $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                                $valores[] = ['total_importe_trans' => $total_importe_trans, 'total_valor_recibo' => $total_valor_recibo];
                                unlink($file);
                                $k++;


                                break;
                            case 'RECA':

                                $query_ruta = ArchivosCargadosRecaudo::where('RUTA', '=', $filename)->first();

                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                    break;
                                }
                                $result = new ArchivosCargadosRecaudo();
                                move_uploaded_file($tempFile, $file);

                                switch ($mes_consolidado) {
                                    case "Enero":
                                        $id_mes = 1;
                                        break;
                                    case "Febrero":
                                        $id_mes = 2;
                                        break;
                                    case "Marzo":
                                        $id_mes = 3;
                                        break;
                                    case "Abril":
                                        $id_mes = 4;
                                        break;
                                    case "Mayo":
                                        $id_mes = 5;
                                        break;
                                    case "Junio":
                                        $id_mes = 6;
                                        break;
                                    case "Julio":
                                        $id_mes = 7;
                                        break;
                                    case "Agosto":
                                        $id_mes = 8;
                                        break;
                                    case "Septiembre":
                                        $id_mes = 9;
                                        break;
                                    case "Octubre":
                                        $id_mes = 10;
                                        break;
                                    case "Noviembre":
                                        $id_mes = 11;
                                        break;
                                    case "Diciembre":
                                        $id_mes = 12;
                                        break;
                                }
                                // SE GURADA EL ARCHIVO EN LA TABLA DE ARCHIVOS
                                $result->ANO_FACTURA = $ano_factura;
                                $result->ID_MES_FACTURA = $id_mes;
                                $result->MES_FACTURA = $mes_factura;
                                $result->DEPARTAMENTO = $departamento;
                                $result->MUNICIPIO = $municipio;
                                $result->OPERADOR_RED = $operador_red;
                                $result->RUTA = $filename;
                                $result->FECHA_CREACION = $fecha_creacion;
                                $result->ID_USUARIO = $id_usuario;
                                $result->save();

                                $query_filename = ArchivosCargadosRecaudo::where('RUTA', '=', $filename)->first();
                                $id_tabla_ruta = $query_filename->ID_TABLA;


                                $importe_trans = 0;
                                $total_valor_recibo = 0;
                                $total_importe_trans = 0;
                                $data = file($file);
                                $row = array();
                                $i = 0;

                                foreach ($data as $lines) {


                                    $row[] = explode("\t", $lines);
                                    $fecha_proc_reg = trim(substr($row[$i][0], 0, 4) . "-" . substr($row[$i][0], 4, 2) . "-" . substr($row[$i][0], 6, 2));
                                    $cod_oper_cont = trim($row[$i][1]);
                                    $nic = trim($row[$i][2]);
                                    $nis = trim($row[$i][3]);
                                    $sec_nis = trim($row[$i][4]);
                                    $sec_rec = trim($row[$i][5]);
                                    $fecha_fact_lect = substr($row[$i][6], 0, 4) . "-" . substr($row[$i][6], 4, 2) . "-" . substr($row[$i][6], 6, 2);
                                    //CODIGO NUEVO

                                    $nombre_cliente = strtoupper(trim($row[$i][7]));
                                    $query_tipo_cliente = TipoCliente::where('NOMBRE', '=', $nombre_cliente)->first();
                                    $id_tipo_cliente = $query_tipo_cliente->ID_TIPO_CLIENTE;

                                    $nombre_tarifa = strtoupper(trim($row[$i][8]));
                                    $query_tarifa = Tarifa::where('NOMBRE', '=', $nombre_tarifa)->first();
                                    $id_tarifa = $query_tarifa->ID_TARIFA;

                                    $id_estado_contrato = trim($row[$i][9]);
                                    $concepto = trim($row[$i][10]);
                                    $importe_trans = trim(str_replace(",", ".", $row[$i][11]));
                                    $total_importe_trans = $total_importe_trans + $importe_trans;

                                    $fecha_trans = substr($row[$i][12], 0, 4) . "-" . substr($row[$i][12], 4, 2) . "-" . substr($row[$i][12], 6, 2);
                                    $valor_recibo = trim(str_replace(",", ".", $row[$i][13]));
                                    $total_valor_recibo = $total_valor_recibo + $row[$i][13];
                                    $id_sector_dpto = trim($row[$i][14]);
                                    $cod_mpio = trim($row[$i][15]);
                                    switch ($cod_mpio) {
                                        case '286':
                                            $id_cod_mpio = 1;
                                            break;
                                        case '264':
                                            $id_cod_mpio = 1;
                                            break;
                                        default:
                                            $id_cod_mpio = $cod_mpio;
                                            break;
                                    }

                                    $id_cod_correg = trim($row[$i][16]);
                                    $cod_depto = trim($row[$i][17]);
                                    switch ($cod_depto) {
                                        case '4':
                                            $id_cod_depto = 1;
                                            break;
                                        case '24':
                                            $id_cod_depto = 54;
                                            break;
                                        case '21':
                                            $id_cod_depto = 41;
                                            break;
                                        default:
                                            $id_cod_depto = $cod_depto;
                                            break;
                                    }

                                    switch ($id_cod_depto) {
                                        case '1':
                                        case '3':
                                        case '6':
                                        case '7':
                                        case '41':
                                        case '54':
                                            $simbolo_variable = trim($row[$i][18]);
                                            break;
                                        default:
                                            $simbolo_variable = 0;
                                            break;
                                    }

                                    // SAVE INFORMATION
                                    $recaudo_values = array(

                                        'FECHA_PROC_REG' => $fecha_proc_reg,
                                        'COD_OPER_CONT' => $cod_oper_cont,
                                        'NIC' => $nic,
                                        'NIS' => $nis,
                                        'SEC_NIS' => $sec_nis,
                                        'SEC_REC' => $sec_rec,
                                        'FECHA_FACT_LECT' => $fecha_fact_lect,
                                        'ID_TIPO_CLIENTE' => $id_tipo_cliente,
                                        'ID_TARIFA' => $id_tarifa,
                                        'ID_ESTADO_CONTRATO' => $id_estado_contrato,
                                        'CONCEPTO' => $concepto,
                                        'IMPORTE_TRANS' => $importe_trans,
                                        'FECHA_TRANS' => $fecha_trans,
                                        'VALOR_RECIBO' => $valor_recibo,
                                        'ID_SECTOR_DPTO' => $id_sector_dpto,
                                        'ID_COD_MPIO' => $id_cod_mpio,
                                        'ID_COD_CORREG' => $id_cod_correg,
                                        'ID_COD_DPTO' => $id_cod_depto,
                                        'SIMBOLO_VARIABLE' => $simbolo_variable,
                                        'ID_TIPO_POBLACION' => $id_tipo_poblacion,
                                        'ANO_FACTURA' => $ano_factura,
                                        'MES_FACTURA' => $id_mes,
                                        'ID_TABLA_RUTA' => $id_tabla_ruta,
                                        'FECHA_CREACION' => $fecha_creacion,
                                        'ID_USUARIO' => $id_usuario,
                                        'OPERADOR_RED' => $operador_red,
                                    );
                                    DB::table($table_recaudo)->insert($recaudo_values);
                                    $i++;
                                }
                                // FIN FOREACH LINE
                                $consultas[] = DB::table($table_recaudo)
                                    ->select([
                                        DB::raw('COUNT(*) AS TOTAL'),
                                        DB::raw('SUM(IMPORTE_TRANS) AS TOTAL_IMPORTE_TRANS'),
                                        DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBIDO')
                                    ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();
                                $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                                $valores[] = ['total_importe_trans' => $total_importe_trans, 'total_valor_recibo' => $total_valor_recibo];
                                unlink($file);
                                $k++;

                                break;
                            case 'REFA':

                                $query_ruta = ArchivosCargadosRefacturacion::where('RUTA', '=', $filename)->first();
                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                    break;
                                }
                                $result = new ArchivosCargadosRefacturacion();
                                move_uploaded_file($tempFile, $file);

                                switch ($mes_consolidado) {
                                    case "Enero":
                                        $id_mes = 1;
                                        break;
                                    case "Febrero":
                                        $id_mes = 2;
                                        break;
                                    case "Marzo":
                                        $id_mes = 3;
                                        break;
                                    case "Abril":
                                        $id_mes = 4;
                                        break;
                                    case "Mayo":
                                        $id_mes = 5;
                                        break;
                                    case "Junio":
                                        $id_mes = 6;
                                        break;
                                    case "Julio":
                                        $id_mes = 7;
                                        break;
                                    case "Agosto":
                                        $id_mes = 8;
                                        break;
                                    case "Septiembre":
                                        $id_mes = 9;
                                        break;
                                    case "Octubre":
                                        $id_mes = 10;
                                        break;
                                    case "Noviembre":
                                        $id_mes = 11;
                                        break;
                                    case "Diciembre":
                                        $id_mes = 12;
                                        break;
                                }
                                // SE GURADA EL ARCHIVO EN LA TABLA DE ARCHIVOS
                                $result->ANO_FACTURA = $ano_factura;
                                $result->ID_MES_FACTURA = $id_mes;
                                $result->MES_FACTURA = $mes_factura;
                                $result->DEPARTAMENTO = $departamento;
                                $result->MUNICIPIO = $municipio;
                                $result->OPERADOR_RED = $operador_red;
                                $result->RUTA = $filename;
                                $result->FECHA_CREACION = $fecha_creacion;
                                $result->ID_USUARIO = $id_usuario;
                                $result->save();

                                $query_filename = ArchivosCargadosRefacturacion::where('RUTA', '=', $filename)->first();
                                $id_tabla_ruta = $query_filename->ID_TABLA;

                                $importe_trans = 0;
                                $total_valor_recibo = 0;
                                $total_importe_trans = 0;
                                $data = file($file);
                                $row = array();
                                $i = 0;

                                foreach ($data as $lines) {

                                    $row[] = explode("\t", $lines);
                                    $fecha_proc_reg = trim(substr($row[$i][0], 0, 4) . "-" . substr($row[$i][0], 4, 2) . "-" . substr($row[$i][0], 6, 2));
                                    $cod_oper_cont = trim($row[$i][1]);
                                    $nic = trim($row[$i][2]);
                                    $nis = trim($row[$i][3]);
                                    $sec_nis = trim($row[$i][4]);
                                    $sec_rec = trim($row[$i][5]);
                                    $fecha_fact_lect = substr($row[$i][6], 0, 4) . "-" . substr($row[$i][6], 4, 2) . "-" . substr($row[$i][6], 6, 2);

                                    $nombre_cliente = strtoupper(trim($row[$i][7]));
                                    $query_tipo_cliente = TipoCliente::where('NOMBRE', '=', $nombre_cliente)->first();
                                    $id_tipo_cliente = $query_tipo_cliente->ID_TIPO_CLIENTE;



                                    $nombre_tarifa = strtoupper(trim($row[$i][8]));
                                    $query_tarifa = Tarifa::where('NOMBRE', '=', $nombre_tarifa)->first();
                                    $id_tarifa = $query_tarifa->ID_TARIFA;
                                    $id_estado_contrato = trim($row[$i][9]);
                                    $concepto = trim($row[$i][10]);
                                    $importe_trans = trim(str_replace(",", ".", $row[$i][11]));
                                    $total_importe_trans = $total_importe_trans + $importe_trans;

                                    $fecha_trans = substr($row[$i][12], 0, 4) . "-" . substr($row[$i][12], 4, 2) . "-" . substr($row[$i][12], 6, 2);
                                    $valor_recibo = trim(str_replace(",", ".", $row[$i][13]));
                                    $total_valor_recibo = $total_valor_recibo + $row[$i][13];
                                    $id_sector_dpto = trim($row[$i][14]);

                                    $cod_mpio = trim($row[$i][15]);
                                    switch ($cod_mpio) {
                                        case '286':
                                            $id_cod_mpio = 1;
                                            break;
                                        case '264':
                                            $id_cod_mpio = 1;
                                            break;
                                        default:
                                            $id_cod_mpio = $cod_mpio;
                                            break;
                                    }

                                    $id_cod_correg = trim($row[$i][16]);
                                    $cod_depto = trim($row[$i][17]);
                                    switch ($cod_depto) {
                                        case '4':
                                            $id_cod_depto = 1;
                                            break;
                                        case '24':
                                            $id_cod_depto = 54;
                                            break;
                                        case '21':
                                            $id_cod_depto = 41;
                                            break;
                                        default:
                                            $id_cod_depto = $cod_depto;
                                            break;
                                    }
                                    switch ($id_cod_depto) {
                                        case '1':
                                        case '3':
                                        case '6':
                                        case '7':
                                        case '41':
                                        case '54':
                                            $simbolo_variable = trim($row[$i][18]);
                                            break;
                                        default:
                                            $simbolo_variable = 0;
                                            break;
                                    }


                                    // SAVING INFORMATION
                                    $refacturacion_values = array(
                                        'FECHA_PROC_REG' => $fecha_proc_reg,
                                        'COD_OPER_CONT' => $cod_oper_cont,
                                        'NIC' => $nic,
                                        'NIS' => $nis,
                                        'SEC_NIS' => $sec_nis,
                                        'SEC_REC' => $sec_rec,
                                        'FECHA_FACT_LECT' => $fecha_fact_lect,
                                        'ID_TIPO_CLIENTE' => $id_tipo_cliente,
                                        'ID_TARIFA' => $id_tarifa,
                                        'ID_ESTADO_CONTRATO' => $id_estado_contrato,
                                        'CONCEPTO' => $concepto,
                                        'IMPORTE_TRANS' => $importe_trans,
                                        'FECHA_TRANS' => $fecha_trans,
                                        'VALOR_RECIBO' => $valor_recibo,
                                        'ID_SECTOR_DPTO' => $id_sector_dpto,
                                        'ID_COD_MPIO' => $id_cod_mpio,
                                        'ID_COD_CORREG' => $id_cod_correg,
                                        'ID_COD_DPTO' => $id_cod_depto,
                                        'SIMBOLO_VARIABLE' => $simbolo_variable,
                                        'ID_TIPO_POBLACION' => $id_tipo_poblacion,
                                        'ANO_FACTURA' => $ano_factura,
                                        'MES_FACTURA' => $id_mes,
                                        'ID_TABLA_RUTA' => $id_tabla_ruta,
                                        'FECHA_CREACION' => $fecha_creacion,
                                        'ID_USUARIO' => $id_usuario,
                                        'OPERADOR_RED' => $operador_red
                                    );
                                    DB::table($table_refacturacion)->insert($refacturacion_values);
                                    $i++;
                                }
                                // FIN FOREACH LINE

                                $consultas[] = DB::table($table_refacturacion)
                                    ->select([
                                        DB::raw('COUNT(*) AS TOTAL'),
                                        DB::raw('SUM(IMPORTE_TRANS) AS TOTAL_IMPORTE_TRANS'),
                                        DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBO')
                                    ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();
                                $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                                $valores[] = ['total_importe_trans' => $total_importe_trans, 'total_valor_recibo' => $total_valor_recibo];
                                unlink($file);
                                $k++;

                                break;
                        }
                        // FIN SWITCH CASE INICIALES ARCHIVOS
                    }
                    // FIN FOREACH FILES
                    return ["resultado" => $consultas, "mensajes" => $mensajes, "elementos" => $elementos, 'valores' => $valores];
                    // ENVIO DE RESPUESTAS
                    break;
            }
            // FIN OPERADORES RED
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function store(Request $request)
    {

        function stripAccent($str)
        {
            return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
        }

        if ($request->file('file')) {

            Validator::make($request->all(), [
                'title' => ['required'],
                'file' => ['required'],
            ])->validate();

            $temFile = $request->file('file');
            $filename = $temFile->getClientOriginalName();
            $filepath = public_path('uploads/');
            $file = $filepath . $filename;
            // move_uploaded_file($_FILES['file']['tmp_name'], $filepath.$filename);
            // se guarda el archivo
            move_uploaded_file($temFile, $file);
            $data = file($file);
            $row = array();
            $i = 0;

            foreach ($data as $line) {
                // se delimita por ; y se guarda la info
                $row[] = explode("\t", $line);

                $title = strtoupper(trim(str_replace(array("#", ".", "'", ";", "/", "\\"), "", stripAccents($row[$i][6]))));
                $name = strtoupper(trim(str_replace(array("#", ".", "'", ";", "/", "\\"), '',  stripAccents($row[$i][8]))));
                File::create([
                    'title' => $title,
                    'name' => $name
                ]);
                $i++;
            };
            //unlink($file); // se elimina el archivo
            return ("<script>alert('Archivo cargado')</script>");
        } else {
            return ("<script>alert('No hay archivo')</script>");
        }
    }
}
