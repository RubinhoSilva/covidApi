<?php

namespace App\Http\Controllers;

use App\Device;
use App\Localizacao;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Geocoder\Laravel\ProviderAndDumperAggregator as Geocoder;

class DeviceController extends Controller
{
    public function cadastrar(Request $request){
        $header = array(
            'Content-Type' => 'application/json; charset=UTF-8',
            'charset' => 'utf-8'
        );

        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'idDevice' => 'required|max:191|unique:tb_device',
            'plataforma' => 'required|max:7',
            'localizacao' => 'required',
            'horario' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['erros' => $validator->errors()], 206, $header, JSON_UNESCAPED_UNICODE);
        }

        $device = new Device($data);
        $device->save();

        $coordenadas = explode(',', $data['localizacao']);
        $resultado = app('geocoder')->reverse($coordenadas[0], $coordenadas[1])->toJson();
        var_dump(json_decode($resultado)['properties']->locality);
        //latitude, longitude

        $localizacao = new Localizacao();
        $localizacao->cidade = $resultado;
        $localizacao->dados = $data['localizacao'];
        $localizacao->horario = $data['horario'];
        $localizacao->idDevice = $data['idDevice'];
        $localizacao->save();
    }
}
