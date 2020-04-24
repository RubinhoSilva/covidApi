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
        $cidades = Localizacao::where('idDevice', Auth::id())->distinct('cidade')->get();
        $minhasLocalizacoes = Localizacao::where('idDevice', Auth::id())->get();

        foreach ($cidades as $cidade){
            $idsDeviceCidades = Localizacao::where('cidade', $cidade)->distinct('idDevice')->get();

            foreach ($idsDeviceCidades as $idDevice){
                $deviceLocalizacoes = Localizacao::where('idDevice', $idDevice)->get();

                foreach ($minhasLocalizacoes as $minhaLocalizacao) {
                    foreach ($deviceLocalizacoes as $deviceLocalizacao){
                        $m = Haversini::calculate(
                            $minhaLocalizacao->latitude,
                            $minhaLocalizacao->longitude,
                            $deviceLocalizacao->latitude,
                            $deviceLocalizacao->longitude,
                            'm'
                        );

                        if($m < 15){
                            $device = Device::find($idDevice);
                            $device->enviarNotificao($device->token, "teste", "teste");

                            VerificaDistancia::dispatch($idDevice);
                        }
                    }
                }
            }
        }

    }
}
