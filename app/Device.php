<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Device extends Authenticatable  implements JWTSubject
{
    use SoftDeletes;

    protected $table = 'tb_device';
    protected $primaryKey = 'idDevice';
    public $timestamps = false;

    protected $fillable = [
        'idDevice', 'plataforma', 'status'
    ];

    protected $hidden = [
        'password'
    ];

    public function localizacoes(){
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
}
