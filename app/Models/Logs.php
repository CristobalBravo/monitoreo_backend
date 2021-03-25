<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as MongoModel;

class Logs extends MongoModel
{
    protected $connection = 'mongodb';
    protected $collection = 'logs';
    public $timestamps = false;
}
