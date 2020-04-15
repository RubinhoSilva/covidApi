<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use SoftDeletes;

    protected $table = 'tb_device';
    protected $primaryKey = 'idDevice';

    protected $fillable = [
        'plataforma', 'status'
    ];

    protected $hidden = [
        'idDevice'
    ];

    public function localizacoes(){
        return $this->hasMany('App\Localizacao', 'idDevice');
    }
}
