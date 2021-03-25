<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as MongoModel;

class ConfiguracionSugerencia extends MongoModel
{
    protected $connection = 'mongodb';
    protected $collection = 'configuracion_sugerencias';
    public $timestamps = false;
}
