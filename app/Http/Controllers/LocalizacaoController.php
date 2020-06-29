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
    public function atualizar(Request $request)
    {
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

        $device = Device::find(Auth::id());

        $localCidade = $device->localizacoes()->orderBy('idLocalizacao', 'desc')->first();

        if($localCidade != null){
            $coordenadasAntigas = explode(',', $localCidade->dados);
            $coordenadasNovas = explode(',', $data['localizacao']);
            $km = Haversini::calculate(
                $coordenadasAntigas[0],
                $coordenadasAntigas[1],
                $coordenadasNovas[0],
                $coordenadasNovas[1],
                'km'
            );

            if ($km <= 4) {
                $cidade = $localCidade->cidade;
            } else {
                $localCidade = Localizacao::where('dados', $data['localizacao'])->orderBy('idLocalizacao', 'desc')->first();

                if ($localCidade != null) {
                    $cidade = $localCidade->cidade;
                } else {
                    $coordenadas = explode(',', $data['localizacao']);
                    $resultado = app('geocoder')->reverse($coordenadas[0], $coordenadas[1])->toJson();
                    //latitude, longitude

                    $cidade = json_decode($resultado)->properties->locality;
                }
            }
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
        $localizacao->idDevice = Auth::id();
        $localizacao->save();

        return response()->json([
            'mensagem' => "Atualizado com sucesso!"
        ], 200, $header);
    }

    public function teste(Request $request)
    {
        $cidades = Localizacao::where('idDevice', Auth::id())->select('cidade')->distinct()->get();

        foreach ($cidades as $cidade) {
            $idsDeviceCidades = Localizacao::where('cidade', $cidade->cidade)->select('idDevice')->distinct()->get();

            foreach ($idsDeviceCidades as $idDevice) {
                $minhasLocalizacoes = Localizacao::where('idDevice', Auth::id())->where('cidade', $cidade->cidade)->get();
                $deviceLocalizacoes = Localizacao::where('idDevice', $idDevice->idDevice)->where('cidade', $cidade->cidade)->select('dados')->get();

                foreach ($minhasLocalizacoes as $minhaLocalizacao) {
                    $coordenadasMinhas = explode(',', $minhaLocalizacao->dados);
                    foreach ($deviceLocalizacoes as $deviceLocalizacao) {
                        $coordenadasDevice = explode(',', $deviceLocalizacao->dados);
                        $m = Haversini::calculate(
                            $coordenadasMinhas[0],
                            $coordenadasMinhas[1],
                            $coordenadasDevice[0],
                            $coordenadasDevice[1],
                            'm'
                        );

                        print("idDevice $idDevice->idDevice\n");
                        print("$m\n");
                        if ($m < 20) {
                            $device = Device::find($idDevice->idDevice);

                            if (!($device->status == 1 || $device->status == 2)) {
                                $device->enviarNotificacao($device->token, "teste", "teste");
                                $device->status = 1;
                                $device->save();
                            }

//                            var_dump($device[0]->token);

                            break;
                        }
                    }
                }
            }
        }

//        $device = Device::find(Auth::id());
//        $device->enviarNotificacao($device['token'], "teste", "teste");
    }
}
