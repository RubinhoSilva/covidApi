<?php

namespace App\Http\Controllers;

use App\Device;
use App\Localizacao;
use Haversini\Haversini;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LocalizacaoController extends Controller
{
    public function atualizar(Request $request){
        $header = array(
            'Content-Type' => 'application/json; charset=UTF-8',
            'charset' => 'utf-8'
        );

        $data = $request->json()->all();

        $validator = Validator::make($data, [
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

        $device =  Device::find(Auth::id());

        $localCidade = $device->localizacoes()->orderBy('idLocalizacao', 'desc')->first();
        $coordenadasAntigas = explode(',', $localCidade->dados);
        $coordenadasNovas = explode(',', $data['localizacao']);
        $km = Haversini::calculate(
            $coordenadasAntigas[0],
            $coordenadasAntigas[1],
            $coordenadasNovas[0],
            $coordenadasNovas[1],
            'km'
        );

        if($km <= 4){
            $cidade = $localCidade->cidade;
        }else{
            $localCidade = Localizacao::where('dados', $data['localizacao'])->orderBy('idLocalizacao', 'desc')->first();

            if($localCidade != null){
                $cidade = $localCidade->cidade;
            }else{
                $coordenadas = explode(',', $data['localizacao']);
                $resultado = app('geocoder')->reverse($coordenadas[0], $coordenadas[1])->toJson();
                //latitude, longitude

                $cidade = json_decode($resultado)->properties->locality;
            }
        }

        var_dump(Auth::user());
        var_dump(Auth::id());

        $localizacao = new Localizacao();
        $localizacao->cidade = $cidade;
        $localizacao->dados = $data['localizacao'];
        $localizacao->horario = $data['horario'];
        $localizacao->idDevice = Auth::user()->idDevice;
        $localizacao->save();
    }
}
