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
        $tempFile = $request->file('file');
        $filename = $tempFile->getClientOriginalName();
        $filepath = public_path('uploads/');
        $file = $filepath . $filename;

        /**
         ** upload file in public directory
         *
         */

        move_uploaded_file($tempFile, $file);
        $fecha_creacion = date('Y-m-d');

        // INSTANCES
        $result = new ArchivosCargadosCatastro();
        $catastro = new CatastroAgosto2022_2();
        $tarifa = new Tarifa();

        $query_ruta = DB::table('archivos_cargados_catastro_2')->where('RUTA', '=', $filename)->first();

        if ($query_ruta) {
            return ["msj" => "El archivo ya existe", "file" => $file, "Status" => false];
        } else {
            $result->ANO_FACTURA = '2022';
            $result->ID_MES_FACTURA = '08';
            $result->MES_FACTURA = 'AGOSTO';
            $result->DEPARTAMENTO = 'BOLIVAR';
            $result->MUNICIPIO = 'ARJONA';
            $result->OPERADOR_RED = 'AFINIA';
            $result->RUTA = $filename;
            $result->FECHA_CREACION = $fecha_creacion;
            $result->ID_USUARIO = 1;
            $result->save();

            $query_filename = DB::table('archivos_cargados_catastro_2')->where('RUTA', '=', $filename)->first();

            $id_tabla_ruta = $query_filename->ID_TABLA;

            $data = file($file);
            $row = array();
            $i = 0;
            foreach ($data as $line) {
                $row[] = explode('|', $line);
                $query_tipo_servicio = DB::table('tipo_servicios_2')->where('COD_TIPO_SERVICIO', '=', trim($row[$i][0]))->select('ID_TIPO_SERVICIO')->first();

                $nombre_tarifa = strtoupper(str_replace(" ", "_", trim($row[$i][3])));
                $query_tarifa = DB::table('tarifas_2')->where('NOMBRE', '=', $nombre_tarifa)->select('ID_TARIFA')->first();


                $nombre_propietario = strtoupper(trim(str_replace("'", "", $row[$i][6])));
                $direccion_vivienda = strtoupper(trim(str_replace("'", "", $row[$i][7])));
                $consumo_facturado = trim(str_replace(",", ".", $row[$i][8]));

                $municipio =  strtoupper(str_replace("_", " ", trim($row[$i][9])));
                $query_municipio = DB::table('municipios_2')->where('NOMBRE', '=', $municipio)->first();
                $id_departamento = $query_municipio->ID_DEPARTAMENTO;
                $id_municipio = $query_municipio->ID_MUNICIPIO;

                $corregimiento = strtoupper(trim(utf8_encode($row[$i][10])));
                $query_corregimiento = DB::table('corregimientos_2')->where('ID_DEPARTAMENTO', '=', $id_departamento)->where('ID_MUNICIPIO', '=', $id_municipio)->where('NOMBRE', '=', $corregimiento)->first();
                $id_corregimiento = $query_corregimiento->ID_TABLA;

                $estado_suministro = strtoupper(trim($row[$i][13]));
                $query_estado_suministro = DB::table('estados_suministro_2')->where('NOMBRE', '=', $estado_suministro)->first();
                $id_estado_suministro = $query_estado_suministro->ID_ESTADO_SUMINISTRO;

                $catastro->ID_TIPO_SERVICIO = $query_tipo_servicio->ID_TIPO_SERVICIO;
                $catastro->ID_TARIFA = $query_tarifa->ID_TARIFA;
                $catastro->NIC = $row[$i][4];
                $catastro->NIS = $row[$i][5];
                $catastro->NOMBRE_PROPIETARIO = $nombre_propietario;
                $catastro->DIRECCION_VIVIENDA = $direccion_vivienda;
                $catastro->CONSUMO_FACTURADO = $consumo_facturado;
                $catastro->ID_COD_DPTO = $id_departamento;
                $catastro->ID_COD_MPIO = $id_municipio;
                $catastro->ID_COD_CORREG = $id_corregimiento;
                $catastro->DEUDA_CORRIENTE = trim(str_replace(",", ".", $row[$i][11]));
                $catastro->DEUDA_CUOTA = trim(str_replace(",", ".", $row[$i][12]));
                $catastro->ID_ESTADO_SUMINISTRO = $id_estado_suministro;
                $catastro->ANO_CATASTRO = '2022';
                $catastro->MES_CATASTRO = '08';
                $catastro->ID_TABLA_RUTA = $id_tabla_ruta;
                $catastro->FECHA_CREACION = $fecha_creacion;
                $catastro->ID_USUARIO = 1;
                $catastro->OPERADOR_RED = 'AFINIA';
                //echo($query_tipo_servicio[0])  ;
                $catastro->save();
                return $query_tipo_servicio;
                // return $query_tipo_servicio;

            }
            unlink($file);
        }



        // if ($result->save()) {
        //     return ["msj" => "Se ha registrado con exito", "file" => $result->RUTA, "Status" => true];
        // } else {
        //     return ["msj" => "Ocurrio un error"];
        // }
        // return("<script>alert('Archivo cargado')</script>");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function store(Request $request)
    {

        function stripAccents($str)
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
