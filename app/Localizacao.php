<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Localizacao extends Model
{
    use SoftDeletes;

    protected $table = 'tb_localizacao';
    protected $primaryKey = 'idLocalizacao';
    public $timestamps = false;
}
