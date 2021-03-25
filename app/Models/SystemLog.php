<?php

namespace App\Models;


//eloquent para modelo relacionales
use Illuminate\Database\Eloquent\Model;
//eloquent para modelo de mongoDB
use Jenssegers\Mongodb\Eloquent\Model as MongoModel;

class SystemLog extends MongoModel{

    protected $table= 'log_system';
    protected $connection = 'mongodb';

}
