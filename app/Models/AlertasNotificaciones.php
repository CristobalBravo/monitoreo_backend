<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as MongoModel;

class AlertasNotificaciones extends MongoModel
{
    protected $connection = 'mongodb';
    protected $collection = 'alertas_notificaciones';
    public $timestamps = false;
}
