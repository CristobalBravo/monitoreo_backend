<?php

namespace App\Http\Controllers;

use App\Models\Logs_user;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PagesStatisticsController extends Controller{
    /*
    * Top 10 Uri que mas utiliza la plataforma
    * Obtiene los 10 Uri que mas utilizan la plataforma
    * de forma ordena y decendente.
    * @Autor Cristobal Bravo
    * @access public
    * @return response
    * En caso de lograr la peticion code 200
    * En caso contrario code 400 con su respectivo mensaje
    */
    public function top(){
        $result = new \stdClass();
        $result->code = 200;
        $start=1605300341;
        $stop = strtotime("-7 day",$start);
        $tops = $this->almacenarEnRedis($stop);
        $data = Array();
        $i=0;
        foreach($tops as $top) {
            $data[$i] = new \stdClass();
            $data[$i]->uri = $top->uri;
            $data[$i]->cantidad = $top->cantidad;
            $i++;
        }
        $result->data = $data;

        return response()->json($result);
    }

    public function topByMonth(){
        $result = new \stdClass();
        $result->code = 200;
        $start=1605300341;
        $stop = strtotime("-1 month",$start);
        $tops = $this->almacenarEnRedis($stop);
        $data = Array();
        $i=0;
        foreach($tops as $top) {
            $data[$i] = new \stdClass();
            $data[$i]->uri = $top->uri;
            $data[$i]->cantidad = $top->cantidad;
            $i++;
        }
        $result->data = $data;

        return response()->json($result);
    }
    public function topByYear(){
        $result = new \stdClass();
        $result->code = 200;
        $start=1605300341;
        $stop = strtotime("-1 year",$start);
        $tops = $this->almacenarEnRedis($stop);
        $data = Array();
        $i=0;
        foreach($tops as $top) {
            $data[$i] = new \stdClass();
            $data[$i]->uri = $top->uri;
            $data[$i]->cantidad = $top->cantidad;
            $i++;
        }
        $result->data = $data;

        return response()->json($result);
    }


    private function obtenerEstadisticaPorPagina($stop){
        $tops = SystemLog::raw(function($collection) use ($stop){
            return $collection->aggregate([
                [
                    '$match' => [
                            'user_id' => ['$ne' => 0 ],
                            'date'    => ['$gte'=> $stop]
                    ]
                ],
                    [
                        '$group' => [
                            '_id' => [
                                'uri' => '$uri'
                            ],
                            'cantidad' => ['$sum' => 1]
                        ]
                    ],
                    ['$project' => [
                            'cantidad' => '$cantidad',
                            'uri' => '$_id.uri',
                            '_id' => 0]
                    ],
                    ['$sort' => ['cantidad' => -1]
                    ],
                    ['$limit'=>10]

                ]);
            });
        return $tops;
    }
    private function almacenarEnRedis($stop){
        if(Cache::has('estadisticas_pages_'.$stop)){
            $tops = Cache::get('estadisticas_pages_'.$stop);
        }else{
            $tops = $this->obtenerEstadisticaPorPagina($stop);
            Cache::put('estadisticas_pages_'.$stop, $tops,config('global.REDIS_TIMER_12HRS'));
        }

        return $tops;

    }
}
