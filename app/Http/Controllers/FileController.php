<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use App\Models\File;
use App\Models\ArchivosCargadosCatastro;
use App\Models\CatastroAgosto2022_2;
use App\Models\Tarifa;
use App\Models\Corregimiento;
use App\Models\EstadoSuministro;



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
            $operador_red = 'AFINIA';
            $mensajes = array();
            $consultas = array();
            $elementos = array();
            switch ($operador_red) {
                case 'AFINIA':
                    foreach ($files as $archivo) {
                        // get the original file name
                        $filename = $archivo->getClientOriginalName();
                        $tempFile = $archivo;
                        $filepath = public_path('uploads/');
                        $file = $filepath . $filename;

                        $iniciales_archivo = substr($filename, 0, 4);
                        switch ($iniciales_archivo) {
                            case 'CATA':

                                //upload file in public directory

                                move_uploaded_file($tempFile, $file);
                                $fecha_creacion = date('Y-m-d');

                                // INSTANCES
                                $result = new ArchivosCargadosCatastro();


                                $query_ruta = DB::table('archivos_cargados_catastro_2')->where('RUTA', '=', $filename)->first();

                                if ($query_ruta) {
                                    $mensajes[] = ['mensaje' => "El archivo ya existe", 'file' => $file];
                                } else {


                                    //array_push( "El archivo ya existe", $file);


                                    // SE GUARDA EL ARCHIVO
                                    $result->ANO_FACTURA = '2022';
                                    $result->ID_MES_FACTURA = '08';
                                    $result->MES_FACTURA = 'AGOSTO';
                                    $result->DEPARTAMENTO = 'BOLIVAR';
                                    $result->MUNICIPIO = 'ARJONA';
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
                                        $query_tipo_servicio = DB::table('tipo_servicios_2')->where('COD_TIPO_SERVICIO', '=', trim($row[$i][0]))->select('ID_TIPO_SERVICIO')->first();

                                        $nombre_tarifa = strtoupper(str_replace(" ", "_", trim($row[$i][3])));
                                        $query_tarifa = DB::table('tarifas_2')->where('NOMBRE', '=', $nombre_tarifa)->select('ID_TARIFA')->first();
                                        $id_tarifa = $query_tarifa->ID_TARIFA;
                                        $nic = $row[$i][4];
                                        $nis = $row[$i][5];

                                        $nombre_propietario = str_replace('?', 'N', utf8_decode(strtoupper(trim( str_replace(array("”", "#", ".", "'", ";", "/", "\\", "`"), "", stripAccents($row[$i][6])))))) ;
                                        $direccion_vivienda = str_replace('?', 'N', utf8_decode(strtoupper(trim( str_replace(array("”", "#", ".", "'", ";", "/", "\\", "`"), "", stripAccents($row[$i][7]))))));


                                        $consumo_facturado = trim(str_replace(",", ".", $row[$i][8]));

                                        $municipio =   strtoupper(str_replace("_", " ", trim($row[$i][9])));
                                        $query_municipio = DB::table('municipios_2')->where('NOMBRE', '=', $municipio)->first();
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
                                            $elementos[] = ['mensaje' => "Corregimiento agregado en la posición '". $i ."' " , 'elemento_agregado' =>  $nombre_corregimiento];
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
                                            $elementos[] = ['mensaje' => "Estado suministro agregado en la posición '". $i ."' " , 'elemento_agregado' =>  $estado_suministro];
                                        }


                                        $total_deuda_corriente = $total_deuda_corriente + $deuda_corriente;
                                        $total_deuda_cuota = $total_deuda_cuota + $deuda_cuota;

                                        $catastro->ID_TIPO_SERVICIO = $query_tipo_servicio->ID_TIPO_SERVICIO;
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
                                        $catastro->ANO_CATASTRO = '2022';
                                        $catastro->MES_CATASTRO = '08';
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
                                // FIN CALIDACION SI EXISTE ARCHIVO
                                break;
                            case 'FACT':
                                break;
                            case 'RECA':
                                break;
                            case 'REFA':
                                break;
                        }
                        // FIN SWITCH CASE INICIALES ARCHIVOS

                    }
                    // FIN FOREACH FILES

                    return ["resultado" => $consultas, "mensajes" => $mensajes, "elementos" => $elementos];

                    // ENVIO DE RESPUESTAS

                    break;
                case 'AIR-E':
                    break;
                case 'ELECTROUILA':
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
