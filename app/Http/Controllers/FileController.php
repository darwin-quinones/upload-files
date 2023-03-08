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
use App\Models\CatastroAgosto2022_2;
use App\Models\FacturacionAgosto2022_2;
use App\Models\RecaudoAgosto2022_2;
use App\Models\RefacturacionAgosto2022_2;
use App\Models\Tarifa;
use App\Models\TarifaAire;
use App\Models\Corregimiento;
use App\Models\EstadoSuministro;
use App\Models\Municipio;
use App\Models\TipoCliente;
use App\Models\TipoClienteAire;
use App\Models\TipoConceptoAire;



class FileController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */

    public function index()
    {
        $files = File::latest()->get();
        return Inertia::render('FileUpload', compact('files'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function fileRegister(Request $request)
    {
        //var_dump($request->files);
        //return $request->files;
        function stripAccents($str)
        {
            return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
        }

        header('Content-Type: text/html; charset=UTF-8');


        if ($request->files) {
            $k = 0;
            $files = $request->files;
            $cod_operador_red = '7';
            $mensajes = array();
            $consultas = array();
            $elementos = array();

            switch ($cod_operador_red) {
                case '8':
                    $operador_red = 'ELECTROUILA';
                    break;
                case '7':
                    $operador_red = 'AIR-E';

                    foreach ($files as $archivo) {
                        $filename = $archivo->getClientOriginalName();
                        $tempFile = $archivo;
                        $filepath = public_path('uploads/');
                        $file = $filepath . $filename;

                        $fecha_creacion = date('Y-m-d');
                        $mes_consolidado = 'Agosto';

                        $id_tipo_poblacion = 1;
                        $ano_factura = '2022';
                        $mes_factura = 'AGOSTO';
                        $departamento = 'LA GUAJIRA';
                        $municipio = 'RIOHACHA';

                        $iniciales_archivo = substr($filename, 0, 4);
                        switch ($iniciales_archivo) {
                            case 'CATA':
                                $result = new ArchivosCargadosCatastro();
                                move_uploaded_file($tempFile, $file);
                                $query_ruta = DB::table('archivos_cargados_catastro_2')->where('RUTA', '=', $filename)->first();

                                if ($query_ruta) {
                                    $mensajes[] = ['mensaje' => "El archivo ya existe", 'file' => $file];
                                } else {

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
                                    $result->ID_USUARIO = 1;
                                    $result->save();


                                    $query_filename = DB::table('archivos_cargados_catastro_2')->where('RUTA', '=', $filename)->first();
                                    $id_tabla_ruta = $query_filename->ID_TABLA;

                                    $total_deuda_corriente = 0;
                                    $total_deuda_cuota = 0;
                                    $data = file($file);
                                    $row = array();
                                    $i = 0;

                                    unset($data[0]);
                                    foreach ($data as $lines) {
                                        // INSTANCES
                                        $catastro = new CatastroAgosto2022_2();
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

                                        $query_municipio = DB::table('municipios_2')->where('NOMBRE', '=', $nombre_municipio)->first();
                                        $id_departamento = $query_municipio->ID_DEPARTAMENTO;
                                        $id_municipio = $query_municipio->ID_MUNICIPIO;

                                        $nombre_corregimiento = strtoupper(trim(utf8_encode($row[$i][12])));

                                        // HAY VECES QUE EL CAMPO DE CORREGIMIENTO VIENE VACIO
                                        if ($nombre_corregimiento == '') {
                                            $query_corregimeinto_sin_nombre = Corregimiento::where('ID_DEPARTAMENTO', '=', $id_departamento)
                                                ->where('ID_MUNICIPIO', '=', $id_municipio)->where('NOMBRE', '=', 'SIN_NOMBRE')->first();
                                        }
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

                                        $deuda_corriente = trim(str_replace(",", ".", $row[$i][13]));
                                        $deuda_cuota = trim(str_replace(",", ".", $row[$i][14]));

                                        $estado_suministro = strtoupper(stripAccents(trim(utf8_encode($row[$i][15]))));
                                        $query_estado_suministro = DB::table('estados_suministro_2')->where('NOMBRE', '=', $estado_suministro)->first();
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

                                        $catastro->ID_TIPO_SERVICIO = $id_tipo_servicio;
                                        $catastro->ID_TARIFA = $id_tarifa;
                                        $catastro->NIC = $nic;
                                        $catastro->NIS = $nis;
                                        $catastro->NOMBRE_PROPIETARIO = $nombre_propietario;
                                        $catastro->DIRECCION_VIVIENDA = $direccion_vivienda;
                                        $catastro->CONSUMO_FACTURADO = $consumo_facturado;
                                        $catastro->ID_COD_DPTO = $id_departamento;
                                        $catastro->ID_COD_MPIO = $id_municipio;
                                        $catastro->ID_COD_CORREG = $id_corregimiento;
                                        $catastro->DEUDA_CORRIENTE = $deuda_corriente;
                                        $catastro->DEUDA_CUOTA = $deuda_cuota;
                                        $catastro->ID_ESTADO_SUMINISTRO = $id_estado_suministro;
                                        $catastro->ANO_CATASTRO = $ano_factura;
                                        $catastro->MES_CATASTRO = $id_mes;
                                        $catastro->ID_TABLA_RUTA = $id_tabla_ruta;
                                        $catastro->FECHA_CREACION = $fecha_creacion;
                                        $catastro->ID_USUARIO = 1;
                                        $catastro->OPERADOR_RED = $operador_red;
                                        $catastro->save();

                                        $i++;
                                    }
                                    // FIN FOREACH LINE
                                    $consultas[] = CatastroAgosto2022_2::select([
                                        DB::raw('COUNT(*) AS TOTAL'),
                                        DB::raw("SUM(DEUDA_CORRIENTE) AS DEUDA_CORRIENTE"),
                                        DB::raw("SUM(DEUDA_CUOTA) AS DEUDA_CUOTA"),
                                    ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();

                                    $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];

                                    unlink($file);
                                    $k++;
                                }
                                // FIN VALIDACION SI EXISTE
                                break;
                            case 'FACT':
                                $result = new ArchivosCargadosFacturacion();
                                move_uploaded_file($tempFile, $file);
                                $query_ruta = ArchivosCargadosFacturacion::where('RUTA', '=', $filename)->first();
                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                } else {
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
                                    $result->ID_USUARIO = 1;
                                    $result->save();

                                    $query_filename = ArchivosCargadosFacturacion::where('RUTA', '=', $filename)->first();
                                    $id_tabla_ruta = $query_filename->ID_TABLA;

                                    $importe_trans = 0;
                                    $total_importe_trans = 0;
                                    $total_valor_recibo = 0;
                                    $data = file($file);
                                    $row = array();
                                    $i = 0;
                                    unset($data[0]);
                                    foreach ($data as $lines) {
                                        // INSTANCES
                                        $facturacion = new FacturacionAgosto2022_2();



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
                                        $query_corregimiento = CatastroAgosto2022_2::where('NIC', '=', $nic)->first();
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

                                        $facturacion->FECHA_PROC_REG = $fecha_proc_reg;
                                        $facturacion->COD_OPER_CONT = $cod_oper_cont;
                                        $facturacion->NIC = $nic;
                                        $facturacion->NIS = $nis;
                                        $facturacion->SEC_NIS = $sec_nis;
                                        $facturacion->SEC_REC = $sec_rec;
                                        $facturacion->FECHA_FACT_LECT = $fecha_fact_lect;
                                        $facturacion->ID_TIPO_CLIENTE = $id_tipo_cliente;
                                        $facturacion->ID_TARIFA = $id_tarifa;
                                        $facturacion->ID_ESTADO_CONTRATO = $id_estado_contrato;
                                        $facturacion->CONCEPTO = $cod_concepto;
                                        $facturacion->IMPORTE_TRANS = $importe_trans;
                                        $facturacion->FECHA_TRANS = $fecha_trans;
                                        $facturacion->VALOR_RECIBO  = $valor_recibo;
                                        $facturacion->ID_SECTOR_DPTO = $id_sector_dpto;
                                        $facturacion->ID_COD_MPIO = $id_cod_mpio;
                                        $facturacion->ID_COD_CORREG = $id_cod_correg;
                                        $facturacion->ID_COD_DPTO = $id_cod_depto;
                                        $facturacion->SIMBOLO_VARIABLE = $simbolo_variable;
                                        $facturacion->CONSUMO_KWH = $consumo_kwh;
                                        $facturacion->ID_TIPO_POBLACION = $id_tipo_poblacion;
                                        $facturacion->ANO_FACTURA = $ano_factura;
                                        $facturacion->MES_FACTURA = $id_mes;
                                        $facturacion->ID_TABLA_RUTA = $id_tabla_ruta;
                                        $facturacion->FECHA_CREACION = $fecha_creacion;
                                        $facturacion->ID_USUARIO = 1;
                                        $facturacion->OPERADOR_RED = $operador_red;
                                        $facturacion->save();
                                        $i++;
                                    }
                                    // FIN FOREACH LINE
                                    $consultas[] = FacturacionAgosto2022_2::select([
                                        DB::raw('COUNT(*) AS TOTAL'),
                                        DB::raw('SUM(IMPORTE_TRANS) AS TOTAL_IMPORTE_TRANS'),
                                        DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBIDO')
                                    ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();

                                    $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];

                                    unlink($file);
                                    $k++;
                                }
                                // FIN VALIDACION SI EXISTE EL ARCHIVO
                                break;
                            case 'RECA':

                                $result = new ArchivosCargadosRecaudo();
                                move_uploaded_file($tempFile, $file);
                                $query_ruta = ArchivosCargadosRecaudo::where('RUTA', '=', $filename)->first();
                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                    break;
                                }
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
                                $result->ID_USUARIO = 1;
                                $result->save();

                                $query_filename = ArchivosCargadosRecaudo::where('RUTA', '=', $filename)->first();
                                $id_tabla_ruta = $query_filename->ID_TABLA;


                                $importe_trans = 0;
                                $total_importe_trans = 0;
                                $total_valor_recibo = 0;
                                $data = file($file);
                                $row = array();
                                $i = 0;
                                unset($data[0]);
                                foreach ($data as $lines) {
                                    // INSTANCES
                                    $recaudo = new RecaudoAgosto2022_2();
                                    $corregimiento = new Corregimiento();
                                    $suministro = new EstadoSuministro();

                                    $row[] = explode("|", $lines);

                                    $nombre_municipio =   strtoupper(str_replace("_", " ", trim(utf8_decode($row[$i][1]))));
                                    $query_municipio = DB::table('municipios_2')->where('NOMBRE', '=', $nombre_municipio)->first();
                                    $id_cod_depto = $query_municipio->ID_DEPARTAMENTO;
                                    $id_cod_mpio = $query_municipio->ID_MUNICIPIO;

                                    $fecha_proc_reg = trim(substr($row[$i][11], 0, 4) . "-" . substr($row[$i][11], 4, 2) . "-" . substr($row[$i][11], 6, 2));
                                    $cod_oper_cont = strtoupper(trim($row[$i][6]));
                                    $nic = trim($row[$i][7]);
                                    $nis = trim($row[$i][8]);
                                    $sec_nis = trim($row[$i][9]);
                                    $sec_rec = trim($row[$i][10]);
                                    $fecha_fact_lect = substr($row[$i][11], 0, 4) . "-" . substr($row[$i][11], 4, 2) . "-" . substr($row[$i][11], 6, 2);
                                    //CODIGO NUEVO
                                    $ano_periodo_anterior = substr($row[$i][11], 0, 4);
                                    $mes_periodo_anterior = substr($row[$i][11], 4, 2);
                                    //FIN CODIGO NUEVO
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
                                    $query_corregimiento = CatastroAgosto2022_2::where('NIC', '=', $nic)->first();
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

                                    $recaudo->FECHA_PROC_REG = $fecha_proc_reg;
                                    $recaudo->COD_OPER_CONT = $cod_oper_cont;
                                    $recaudo->NIC = $nic;
                                    $recaudo->NIS = $nis;
                                    $recaudo->SEC_NIS = $sec_nis;
                                    $recaudo->SEC_REC = $sec_rec;
                                    $recaudo->FECHA_FACT_LECT = $fecha_fact_lect;
                                    $recaudo->ID_TIPO_CLIENTE = $id_tipo_cliente;
                                    $recaudo->ID_TARIFA = $id_tarifa;
                                    $recaudo->ID_ESTADO_CONTRATO = $id_estado_contrato;
                                    $recaudo->CONCEPTO = $cod_concepto;
                                    $recaudo->IMPORTE_TRANS = $importe_trans;
                                    $recaudo->FECHA_TRANS = $fecha_trans;
                                    $recaudo->VALOR_RECIBO = $valor_recibo;
                                    $recaudo->ID_SECTOR_DPTO = $id_sector_dpto;
                                    $recaudo->ID_COD_MPIO = $id_cod_mpio;
                                    $recaudo->ID_COD_CORREG = $id_cod_correg;
                                    $recaudo->ID_COD_DPTO = $id_cod_depto;
                                    $recaudo->SIMBOLO_VARIABLE = $simbolo_variable;
                                    $recaudo->ID_TIPO_POBLACION = $id_tipo_poblacion;
                                    $recaudo->ANO_FACTURA = $ano_factura;
                                    $recaudo->MES_FACTURA = $id_mes;
                                    $recaudo->ID_TABLA_RUTA = $id_tabla_ruta;
                                    $recaudo->FECHA_CREACION = $fecha_creacion;
                                    $recaudo->ID_USUARIO = 1;
                                    $recaudo->OPERADOR_RED = $operador_red;
                                    $recaudo->save();
                                    $i++;
                                }
                                $consultas[] = RecaudoAgosto2022_2::select([
                                    DB::raw('COUNT(*) AS TOTAL'),
                                    DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBIDO')
                                ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();
                                $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                                unlink($file);
                                $k++;
                                break;
                            case 'REFA':
                                $result = new ArchivosCargadosRefacturacion();
                                move_uploaded_file($tempFile, $file);
                                $query_ruta = ArchivosCargadosRefacturacion::where('RUTA', '=', $filename)->first();
                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                    break;
                                }

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
                                $result->ID_USUARIO = 1;
                                $result->save();

                                $query_filename = ArchivosCargadosRefacturacion::where('RUTA', '=', $filename)->first();
                                $id_tabla_ruta = $query_filename->ID_TABLA;

                                $importe_trans = 0;
                                $total_importe_trans = 0;
                                $total_valor_recibo = 0;
                                $data = file($file);
                                $row = array();
                                $i = 0;
                                unset($data[0]);
                                foreach ($data as $lines) {
                                    $refacturacion = new RefacturacionAgosto2022_2();
                                    $row[] = explode("|", $lines);

                                    $nombre_municipio =   strtoupper(str_replace("_", " ", trim(utf8_decode($row[$i][1]))));
                                    $query_municipio = DB::table('municipios_2')->where('NOMBRE', '=', $nombre_municipio)->first();
                                    $id_cod_depto = $query_municipio->ID_DEPARTAMENTO;
                                    $id_cod_mpio = $query_municipio->ID_MUNICIPIO;

                                    $fecha_proc_reg = trim(substr($row[$i][11], 0, 4) . "-" . substr($row[$i][11], 4, 2) . "-" . substr($row[$i][11], 6, 2));
                                    $cod_oper_cont = strtoupper(trim($row[$i][6]));
                                    $nic = trim($row[$i][7]);
                                    $nis = trim($row[$i][8]);
                                    $sec_nis = trim($row[$i][9]);
                                    $sec_rec = trim($row[$i][10]);

                                    $fecha_fact_lect = substr($row[$i][11], 0, 4) . "-" . substr($row[$i][11], 4, 2) . "-" . substr($row[$i][11], 6, 2);
                                    //CODIGO NUEVO
                                    $ano_periodo_anterior = substr($row[$i][11], 0, 4);
                                    $mes_periodo_anterior = substr($row[$i][11], 4, 2);
                                    //FIN CODIGO NUEVO
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
                                    $query_corregimiento = CatastroAgosto2022_2::where('NIC', '=', $nic)->first();
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

                                    $refacturacion->FECHA_PROC_REG = $fecha_proc_reg;
                                    $refacturacion->COD_OPER_CONT = $cod_oper_cont;
                                    $refacturacion->NIC = $nic;
                                    $refacturacion->NIS = $nis;
                                    $refacturacion->SEC_NIS = $sec_nis;
                                    $refacturacion->SEC_REC = $sec_rec;
                                    $refacturacion->FECHA_FACT_LECT = $fecha_fact_lect;
                                    $refacturacion->ID_TIPO_CLIENTE = $id_tipo_cliente;
                                    $refacturacion->ID_TARIFA = $id_tarifa;
                                    $refacturacion->ID_ESTADO_CONTRATO = $id_estado_contrato;
                                    $refacturacion->CONCEPTO = $cod_concepto;
                                    $refacturacion->IMPORTE_TRANS = $importe_trans;
                                    $refacturacion->FECHA_TRANS = $fecha_trans;
                                    $refacturacion->VALOR_RECIBO = $valor_recibo;
                                    $refacturacion->ID_SECTOR_DPTO = $id_sector_dpto;
                                    $refacturacion->ID_COD_MPIO = $id_cod_mpio;
                                    $refacturacion->ID_COD_CORREG = $id_cod_correg;
                                    $refacturacion->ID_COD_DPTO = $id_cod_depto;
                                    $refacturacion->SIMBOLO_VARIABLE = $simbolo_variable;
                                    $refacturacion->ID_TIPO_POBLACION = $id_tipo_poblacion;
                                    $refacturacion->ANO_FACTURA = $ano_factura;
                                    $refacturacion->MES_FACTURA = $id_mes;
                                    $refacturacion->ID_TABLA_RUTA = $id_tabla_ruta;
                                    $refacturacion->FECHA_CREACION = $fecha_creacion;
                                    $refacturacion->ID_USUARIO = 1;
                                    $refacturacion->OPERADOR_RED = $operador_red;
                                    $refacturacion->save();
                                    $i++;
                                }
                                // FIN FOREACH LINE
                                $consultas[] = RefacturacionAgosto2022_2::select([
                                    DB::raw('COUNT(*) AS TOTAL'),
                                    DB::raw('SUM(IMPORTE_TRANS) AS TOTAL_IMPORTE_TRANS'),
                                    DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBO')
                                ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();
                                $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                                unlink($file);
                                $k++;
                                break;
                        }
                        // FIN INICIALES ARCHIVOS
                    }
                    // FIN FOREACH FILES
                    return ["resultado" => $consultas, "mensajes" => $mensajes, "elementos" => $elementos];

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

                                // INSTANCES
                                $result = new ArchivosCargadosCatastro();
                                //upload file in public directory
                                move_uploaded_file($tempFile, $file);

                                $query_ruta = DB::table('archivos_cargados_catastro_2')->where('RUTA', '=', $filename)->first();

                                if ($query_ruta) {
                                    $mensajes[] = ['mensaje' => "El archivo ya existe", 'file' => $file];
                                } else {


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
                                    $result->ID_USUARIO = 1;
                                    $result->save();

                                    $query_filename = DB::table('archivos_cargados_catastro_2')->where('RUTA', '=', $filename)->first();

                                    $id_tabla_ruta = $query_filename->ID_TABLA;

                                    $total_deuda_corriente = 0;
                                    $total_deuda_cuota = 0;
                                    $data = file($file);
                                    $row = array();
                                    $i = 0;
                                    //$query_max_id_corregimiento = Corregimiento::where('ID_DEPARTAMENTO', '=', 1)->where('ID_MUNICIPIO', '=', 4)->max('ID_CORREGIMIENTO');
                                    foreach ($data as $lines) {

                                        // INSTANCES
                                        $catastro = new CatastroAgosto2022_2();
                                        $corregimiento = new Corregimiento();
                                        $suministro = new EstadoSuministro();

                                        $row[] = explode('|', $lines);

                                        $nombre_tarifa = strtoupper(str_replace(" ", "_", trim($row[$i][3])));
                                        $query_tarifa = DB::table('tarifas_2')->where('NOMBRE', '=', $nombre_tarifa)->select('ID_TARIFA')->first();
                                        $id_tarifa = $query_tarifa->ID_TARIFA;
                                        $nic = $row[$i][4];
                                        $nis = $row[$i][5];

                                        $nombre_propietario = str_replace('?', 'N', utf8_decode(strtoupper(trim(str_replace(array("”", "#", ".", "'", ";", "/", "\\", "`", '"', "'"), "", stripAccents($row[$i][6]))))));
                                        $direccion_vivienda = str_replace('?', 'N', utf8_decode(strtoupper(trim(str_replace(array("”", "#", ".", "'", ";", "/", "\\", "`", '"', "'"), "", stripAccents($row[$i][7]))))));


                                        $consumo_facturado = trim(str_replace(",", ".", $row[$i][8]));

                                        $nombre_municipio =   strtoupper(str_replace("_", " ", trim($row[$i][9])));
                                        $query_municipio = DB::table('municipios_2')->where('NOMBRE', '=', $nombre_municipio)->first();
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
                                        $query_estado_suministro = DB::table('estados_suministro_2')->where('NOMBRE', '=', $estado_suministro)->first();
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
                                        $query_tipo_servicio = DB::table('tipo_servicios_2')->where('COD_TIPO_SERVICIO', '=', $cod_tipo_servicio)->first();
                                        $id_tipo_servicio = $query_tipo_servicio->ID_TIPO_SERVICIO;
                                        $catastro->ID_TIPO_SERVICIO = $id_tipo_servicio;
                                        $catastro->ID_TARIFA = $id_tarifa;
                                        $catastro->NIC = $nic;
                                        $catastro->NIS = $nis;
                                        $catastro->NOMBRE_PROPIETARIO = $nombre_propietario;
                                        $catastro->DIRECCION_VIVIENDA = $direccion_vivienda;
                                        $catastro->CONSUMO_FACTURADO = $consumo_facturado;
                                        $catastro->ID_COD_DPTO = $id_departamento;
                                        $catastro->ID_COD_MPIO = $id_municipio;
                                        $catastro->ID_COD_CORREG = $id_corregimiento;
                                        $catastro->DEUDA_CORRIENTE = $deuda_corriente;
                                        $catastro->DEUDA_CUOTA = $deuda_cuota;
                                        $catastro->ID_ESTADO_SUMINISTRO = $id_estado_suministro;
                                        $catastro->ANO_CATASTRO = $ano_factura;
                                        $catastro->MES_CATASTRO = $id_mes;
                                        $catastro->ID_TABLA_RUTA = $id_tabla_ruta;
                                        $catastro->FECHA_CREACION = $fecha_creacion;
                                        $catastro->ID_USUARIO = 1;
                                        $catastro->OPERADOR_RED = $operador_red;
                                        $catastro->save();

                                        $i++;
                                    }
                                    // FIN FOREACH LINE

                                    $consultas[] = CatastroAgosto2022_2::select([
                                        DB::raw('COUNT(*) AS TOTAL'),
                                        DB::raw("SUM(DEUDA_CORRIENTE) AS DEUDA_CORRIENTE"),
                                        DB::raw("SUM(DEUDA_CUOTA) AS DEUDA_CUOTA"),
                                    ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();

                                    $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];

                                    unlink($file);
                                    $k++;
                                }
                                // FIN VALIDACION SI EXISTE ARCHIVO
                                break;
                            case 'FACT':
                                $result = new ArchivosCargadosFacturacion();
                                move_uploaded_file($tempFile, $file);
                                $query_ruta = ArchivosCargadosFacturacion::where('RUTA', '=', $filename)->first();

                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                } else {

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
                                    $result->ID_USUARIO = 1;
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
                                        // INSTANCES
                                        $facturacion = new FacturacionAgosto2022_2();
                                        $corregimiento = new Corregimiento();
                                        $suministro = new EstadoSuministro();

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

                                        $facturacion->FECHA_PROC_REG = $fecha_proc_reg;
                                        $facturacion->COD_OPER_CONT = $cod_oper_cont;
                                        $facturacion->NIC = $nic;
                                        $facturacion->NIS = $nis;
                                        $facturacion->SEC_NIS = $sec_nis;
                                        $facturacion->SEC_REC = $sec_rec;
                                        $facturacion->FECHA_FACT_LECT = $fecha_fact_lect;
                                        $facturacion->ID_TIPO_CLIENTE = $id_tipo_cliente;
                                        $facturacion->ID_TARIFA = $id_tarifa;
                                        $facturacion->ID_ESTADO_CONTRATO = $id_estado_contrato;
                                        $facturacion->CONCEPTO = $concepto;
                                        $facturacion->IMPORTE_TRANS = $importe_trans;
                                        $facturacion->FECHA_TRANS = $fecha_trans;
                                        $facturacion->VALOR_RECIBO  = $valor_recibo;
                                        $facturacion->ID_SECTOR_DPTO = $id_sector_dpto;
                                        $facturacion->ID_COD_MPIO = $id_cod_mpio;
                                        $facturacion->ID_COD_CORREG = $id_cod_correg;
                                        $facturacion->ID_COD_DPTO = $id_cod_depto;
                                        $facturacion->SIMBOLO_VARIABLE = $simbolo_variable;
                                        $facturacion->CONSUMO_KWH = $consumo_kwh;
                                        $facturacion->ID_TIPO_POBLACION = $id_tipo_poblacion;
                                        $facturacion->ANO_FACTURA = $ano_factura;
                                        $facturacion->MES_FACTURA = $id_mes;
                                        $facturacion->ID_TABLA_RUTA = $id_tabla_ruta;
                                        $facturacion->FECHA_CREACION = $fecha_creacion;
                                        $facturacion->ID_USUARIO = 1;
                                        $facturacion->OPERADOR_RED = $operador_red;
                                        $facturacion->save();

                                        $i++;
                                    }
                                    // FIN FOREACH LINE
                                    $consultas[] = FacturacionAgosto2022_2::select([
                                        DB::raw('COUNT(*) AS TOTAL'),
                                        DB::raw('SUM(IMPORTE_TRANS) AS TOTAL_IMPORTE_TRANS'),
                                        DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBIDO')
                                    ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();

                                    $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];

                                    unlink($file);
                                    $k++;
                                }

                                break;
                            case 'RECA':
                                $result = new ArchivosCargadosRecaudo();
                                move_uploaded_file($tempFile, $file);
                                $query_ruta = ArchivosCargadosRecaudo::where('RUTA', '=', $filename)->first();

                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                } else {

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
                                    $result->ID_USUARIO = 1;
                                    $result->save();

                                    $query_filename = ArchivosCargadosRecaudo::where('RUTA', '=', $filename)->first();
                                    $id_tabla_ruta = $query_filename->ID_TABLA;


                                    $importe_trans = 0;
                                    $total_importe_trans = 0;
                                    $total_valor_recibo = 0;
                                    $data = file($file);
                                    $row = array();
                                    $i = 0;

                                    foreach ($data as $lines) {
                                        // INSTANCES
                                        $recaudo = new RecaudoAgosto2022_2();
                                        $corregimiento = new Corregimiento();
                                        $suministro = new EstadoSuministro();

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
                                        $recaudo->FECHA_PROC_REG = $fecha_proc_reg;
                                        $recaudo->COD_OPER_CONT = $cod_oper_cont;
                                        $recaudo->NIC = $nic;
                                        $recaudo->NIS = $nis;
                                        $recaudo->SEC_NIS = $sec_nis;
                                        $recaudo->SEC_REC = $sec_rec;
                                        $recaudo->FECHA_FACT_LECT = $fecha_fact_lect;
                                        $recaudo->ID_TIPO_CLIENTE = $id_tipo_cliente;
                                        $recaudo->ID_TARIFA = $id_tarifa;
                                        $recaudo->ID_ESTADO_CONTRATO = $id_estado_contrato;
                                        $recaudo->CONCEPTO = $concepto;
                                        $recaudo->IMPORTE_TRANS = $importe_trans;
                                        $recaudo->FECHA_TRANS = $fecha_trans;
                                        $recaudo->VALOR_RECIBO = $valor_recibo;
                                        $recaudo->ID_SECTOR_DPTO = $id_sector_dpto;
                                        $recaudo->ID_COD_MPIO = $id_cod_mpio;
                                        $recaudo->ID_COD_CORREG = $id_cod_correg;
                                        $recaudo->ID_COD_DPTO = $id_cod_depto;
                                        $recaudo->SIMBOLO_VARIABLE = $simbolo_variable;
                                        $recaudo->ID_TIPO_POBLACION = $id_tipo_poblacion;
                                        $recaudo->ANO_FACTURA = $ano_factura;
                                        $recaudo->MES_FACTURA = $id_mes;
                                        $recaudo->ID_TABLA_RUTA = $id_tabla_ruta;
                                        $recaudo->FECHA_CREACION = $fecha_creacion;
                                        $recaudo->ID_USUARIO = 1;
                                        $recaudo->OPERADOR_RED = $operador_red;
                                        $recaudo->save();
                                        $i++;
                                    }
                                    // FIN FOREACH LINE
                                    $consultas[] = RecaudoAgosto2022_2::select([
                                        DB::raw('COUNT(*) AS TOTAL'),
                                        DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBIDO')
                                    ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();
                                    $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                                    unlink($file);
                                    $k++;
                                }
                                break;
                            case 'REFA':
                                $result = new ArchivosCargadosRefacturacion();
                                move_uploaded_file($tempFile, $file);
                                $query_ruta = ArchivosCargadosRefacturacion::where('RUTA', '=', $filename)->first();
                                if ($query_ruta) {
                                    $mensajes[] = ["mensaje" => "El archivo ya existe", "file" => $file];
                                } else {
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
                                    $result->ID_USUARIO = 1;
                                    $result->save();

                                    $query_filename = ArchivosCargadosRefacturacion::where('RUTA', '=', $filename)->first();
                                    $id_tabla_ruta = $query_filename->ID_TABLA;

                                    $importe_trans = 0;
                                    $total_importe_trans = 0;
                                    $total_valor_recibo = 0;
                                    $data = file($file);
                                    $row = array();
                                    $i = 0;

                                    foreach ($data as $lines) {
                                        $refacturacion = new RefacturacionAgosto2022_2();
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
                                        $refacturacion->FECHA_PROC_REG = $fecha_proc_reg;
                                        $refacturacion->COD_OPER_CONT = $cod_oper_cont;
                                        $refacturacion->NIC = $nic;
                                        $refacturacion->NIS = $nis;
                                        $refacturacion->SEC_NIS = $sec_nis;
                                        $refacturacion->SEC_REC = $sec_rec;
                                        $refacturacion->FECHA_FACT_LECT = $fecha_fact_lect;
                                        $refacturacion->ID_TIPO_CLIENTE = $id_tipo_cliente;
                                        $refacturacion->ID_TARIFA = $id_tarifa;
                                        $refacturacion->ID_ESTADO_CONTRATO = $id_estado_contrato;
                                        $refacturacion->CONCEPTO = $concepto;
                                        $refacturacion->IMPORTE_TRANS = $importe_trans;
                                        $refacturacion->FECHA_TRANS = $fecha_trans;
                                        $refacturacion->VALOR_RECIBO = $valor_recibo;
                                        $refacturacion->ID_SECTOR_DPTO = $id_sector_dpto;
                                        $refacturacion->ID_COD_MPIO = $id_cod_mpio;
                                        $refacturacion->ID_COD_CORREG = $id_cod_correg;
                                        $refacturacion->ID_COD_DPTO = $id_cod_depto;
                                        $refacturacion->SIMBOLO_VARIABLE = $simbolo_variable;
                                        $refacturacion->ID_TIPO_POBLACION = $id_tipo_poblacion;
                                        $refacturacion->ANO_FACTURA = $ano_factura;
                                        $refacturacion->MES_FACTURA = $id_mes;
                                        $refacturacion->ID_TABLA_RUTA = $id_tabla_ruta;
                                        $refacturacion->FECHA_CREACION = $fecha_creacion;
                                        $refacturacion->ID_USUARIO = 1;
                                        $refacturacion->OPERADOR_RED = $operador_red;
                                        $refacturacion->save();
                                        $i++;
                                    }
                                    // FIN FOREACH LINE

                                    $consultas[] = RefacturacionAgosto2022_2::select([
                                        DB::raw('COUNT(*) AS TOTAL'),
                                        DB::raw('SUM(IMPORTE_TRANS) AS TOTAL_IMPORTE_TRANS'),
                                        DB::raw('SUM(VALOR_RECIBO) AS TOTAL_VALOR_RECIBO')
                                    ])->where('ID_TABLA_RUTA', '=', $id_tabla_ruta)->get();
                                    $mensajes[] = ['mensaje' => 'Archivo cargado con exito', 'file' => $file];
                                    unlink($file);
                                    $k++;
                                }
                                // FIN FOREACH FILE
                                break;
                        }
                        // FIN SWITCH CASE INICIALES ARCHIVOS

                    }
                    // FIN FOREACH FILES

                    return ["resultado" => $consultas, "mensajes" => $mensajes, "elementos" => $elementos];

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
