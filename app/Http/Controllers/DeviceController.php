<?php

namespace App\Http\Controllers;

use App\Device;
use App\Localizacao;
use Illuminate\Support\Facades\Hash;
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
        $device->password = Hash::make($data['idDevice']);;
        $device->save();

        $localCidade = Localizacao::where('dados', $data['localizacao'])->orderBy('idLocalizacao', 'desc')->first();

        if($localCidade != null){
            $cidade = $localCidade->cidade;
        }else{
            $coordenadas = explode(',', $data['localizacao']);
            $resultado = app('geocoder')->reverse($coordenadas[0], $coordenadas[1])->toJson();
            //latitude, longitude

            $cidade = json_decode($resultado)->properties->locality;
        }

        $localizacao = new Localizacao();
        $localizacao->cidade = $cidade;
        $localizacao->dados = $data['localizacao'];
        $localizacao->horario = $data['horario'];
        $localizacao->idDevice = $data['idDevice'];
        $localizacao->save();

        $credentials = [
            'idDevice' => $data['idDevice'],
            'password' => $data['idDevice']];


        $token = auth('device')->attempt($credentials);

        var_dump($token);
    }
}
