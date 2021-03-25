<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as MongoModel;
class Logs_user extends MongoModel
{
    protected $connection = 'mongodb';
    protected $collection = 'logs_user';
    public $timestamps = false;
}
