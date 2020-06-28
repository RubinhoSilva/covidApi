<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaravelFCM\Message\Exceptions\InvalidOptionsException;
use Tymon\JWTAuth\Contracts\JWTSubject;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use LaravelFCM\Facades\FCM;

class Device extends Authenticatable implements JWTSubject
{
    /*status:
        0 -> suave
        1 -> monitoramento
        2 -> suspeito*/

    use SoftDeletes;

    protected $table = 'tb_device';
    protected $primaryKey = 'idDevice';
    public $timestamps = false;

    protected $fillable = [
        'device', 'plataforma', 'status', 'token'
    ];

    protected $hidden = [
        'password'
    ];

    public function localizacoes()
    {
        return $this->hasMany('App\Localizacao', 'idDevice');
    }

    /**
     * @inheritDoc
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @inheritDoc
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function enviarNotificacao($token, $titulo, $corpo){
        $optionBuilder = new OptionsBuilder();
        try {
            $optionBuilder->setTimeToLive(60 * 20);
        } catch (InvalidOptionsException $e) {
        }

        $notificationBuilder = new PayloadNotificationBuilder($titulo);
        $notificationBuilder->setBody($corpo)
            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['click_action' => 'FLUTTER_NOTIFICATION_CLICK', 'screen' => ]);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

        // return Array - you must remove all this tokens in your database
        $downstreamResponse->tokensToDelete();

        // return Array (key : oldToken, value : new token - you must change the token in your database)
        $downstreamResponse->tokensToModify();

        // return Array - you should try to resend the message to the tokens in the array
        $downstreamResponse->tokensToRetry();

        // return Array (key:token, value:error) - in production you should remove from your database the tokens
        $downstreamResponse->tokensWithError();
    }
}
