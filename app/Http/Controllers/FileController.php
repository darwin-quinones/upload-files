<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use App\Models\File;

// LIBRERIA PHP SPREADSHEET
require '../vendor/autoload.php';
// require_once('../Includes/Config.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;



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

    public function datos(Request $request)
    {
        return Inertia::render('datos.php', $request);
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
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();


        // validator::make($request->all(), [
        //     'title' => 'required',
        //     'file' => 'required',
        // ])->validate();

        // $fileName = time().'.'.$request->file->extension();
        // $fileName = $request->file->getClientOriginalName().'.xlsx';
        //$fileName = $request->file->getClientOriginalName();
        // Aqui es donde lo guardaria
        //$request->file->move(public_path('uploads'), $fileName);
        // $file = file();

        // SE LEE EL ARCHIVO
        // $spreadsheet = $reader->load($request->file);
        // $sheet_base = $spreadsheet->getSheetByName('BASE');

        // $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file);
        // // $spreadsheet->setDelimiter(';');
        // // $spreadsheet->setEnclosure('');
        // $worksheet = $spreadsheet->getActiveSheet();
        // $data = $worksheet->toArray();

        //var_dump($worksheet.' \n');

        $reader->setDelimiter(';');
        $reader->setEnclosure('');
        $reader->setSheetIndex(0);
        $spreadsheet = $reader->load($request->file);
        var_dump($spreadsheet) ;

        //$number_rows = $sheet_base->getHighestDataRow();

        //$sheetData = $sheet_base->toArray();
        // $r = array();
        // foreach($data as $row){
        //     $r[] = explode(";", $row);
        //     echo $r[0] . ' \n';
        // }
        // echo $sheet_base;
        // $row = array();
        // foreach ($spreadsheet as $row) {
        //     // $row[] = explode(";", $line);

        //     // $nombre_propietario = strtoupper(trim(str_replace(array("#", ".", "'", ";", "/", "\\"), "", stripAccents($row[7]))));
        //     // $direccion_vivienda = strtoupper(trim(str_replace(array(".", "'", ";", "/", "\\"), '',  stripAccents($row[9]))));
        //     // create or save file
        //     File::create([
        //         'title' => $row[1],
        //         'name' => $row[2]
        //     ]);
        // }

        return Inertia::render('file.datos');
    }
}
