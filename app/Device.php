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
    use SoftDeletes;

    protected $table = 'tb_device';
    protected $primaryKey = 'idDevice';
    public $timestamps = false;

    protected $fillable = [
        'device', 'plataforma', 'status'
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

    public function enviarNotificao(){
        $optionBuilder = new OptionsBuilder();
        try {
            $optionBuilder->setTimeToLive(60 * 20);
        } catch (InvalidOptionsException $e) {
        }

        $notificationBuilder = new PayloadNotificationBuilder('my title');
        $notificationBuilder->setBody('Hello world')
            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data']);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $token = "fuY_ezZUES0:APA91bGUNPQO0Y0ER-ywjhlaPLGKa-dYKc5glLUzLYv9FmLkOfc2sX5pWfnfGmCHbouQ6JLppILpZpu-J-20VOkg78MWU5uFWVCL-pdk0b1h5BoDI4lLXIqkwhtz6875z5WqAob2oJkn";

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
