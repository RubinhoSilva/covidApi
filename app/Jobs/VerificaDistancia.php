<?php

namespace App\Jobs;

use App\Device;
use App\Localizacao;
use Haversini\Haversini;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class VerificaDistancia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $idDevice;

    /**
     * Create a new job instance.
     * @param $idDevice
     */
    public function __construct($idDevice)
    {
        $this->idDevice = $idDevice;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
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

                        if($m < 20){
                            $device = Device::find($idDevice);

                            $device->enviarNotificacao($device->token, "teste", "teste");
                        }
                    }
                }
            }
        }

    }
}
