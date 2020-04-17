<?php

namespace App\Http\Controllers;

use App\Device;
use App\Localizacao;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Geocoder\Laravel\ProviderAndDumperAggregator as Geocoder;
use Tymon\JWTAuth\Facades\JWTAuth;

class DeviceController extends Controller
{
    public function cadastrar(Request $request){
        $header = array(
            'Content-Type' => 'application/json; charset=UTF-8',
            'charset' => 'utf-8'
        );

        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'device' => 'required|max:191|unique:tb_device',
            'plataforma' => 'required|max:7',
            'latitude' => 'required',
            'longitude' => 'required',
            'horario' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['erros' => $validator->errors()], 206, $header, JSON_UNESCAPED_UNICODE);
        }

        $latitude = $data['latitude'];
        $longitude = $data['longitude'];

        $data['localizacao'] = "$latitude,$longitude";

        $device = new Device($data);
        $device->password = Hash::make($data['device']);;
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
        $localizacao->idDevice = $device->id;
        $localizacao->save();

        $credentials = [
            'device' => $data['device'],
            'password' => $data['device']];


        $token = auth('device')->attempt($credentials);


        return response()->json([
            'token' => $token,
            'type' => 'bearer',
            'expires' => auth('device')->factory()->getTTL(),
        ], 200, $header);
    }
}
