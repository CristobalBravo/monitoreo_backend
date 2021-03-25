<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MemoryStatisticController extends Controller{

    /*
    * Top 10 uri que mas memoria utiliza en promedio
    * Estadisticas basada en la utilizacion de memoria
    * en promedio que utiliza una Uri en tiempo determinado
    * @public
    * @Autor Cristobal Bravo
    * @return response
    * @params resquest
    * En caso de que la peticion sea exitosa code 200
    * En caso contrario code 400
    */
    public function topMemoryByUri(Request $request){
        $result = new \stdClass();
        $result->code = 200;
        $start=1605300341;
        $tiempo=$request->tiempo;
        $stop = strtotime($tiempo,$start);
        $nombre= "uri";
        try{
            $tops=$this->almacenarRedis($nombre, $stop);
            $data = Array();
            $i=0;
            foreach($tops as $top) {
                $data[$i] =array(strVal($i+1),$top->memory_usage,$top->memory_usage,$top->uri,$top->memory_peak,$top->memory_peak,$top->uri,);
                $i++;
            }
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'no se ha podido realizar la peticion';
        }
        $object = new \stdClass();
        $object->role='annotation';

        $objectText = new \stdClass();
        $objectText->role='annotationText';
        $result->columnNames = array('URI','Memoria usada (Mb)',$object,$objectText, 'Memoria Máxima (Mb)',$object,$objectText);
            $result->datas = $data;

        return response()->json($result);
    }

    /*
    * Top 10 controller que mas memoria utiliza en promedio
    * Estadisticas basada en la utilizacion de memoria
    * en promedio que utiliza una controller en un tiempo
    * determinado
    * @public
    * @Autor Cristobal Bravo
    * @return response
    * @params request
    * En caso de que la peticion sea exitosa code 200
    * En caso contrario code 400
    */
    public function topMemoryByController(Request $request){
        $result = new \stdClass();
        $result->code = 200;
        $start=1605300341;
        $tiempo=$request->tiempo;
        $stop = strtotime($tiempo,$start);
        $nombre= "controller";
        try{
            $tops=$this->almacenarRedis($nombre, $stop);
            $data = Array();
            $i=0;
            foreach($tops as $top) {
                $data[$i] =array(strVal($i+1),$top->memory_usage,$top->memory_usage,$top->controller,$top->memory_peak,$top->memory_peak,$top->controller,);
                $i++;


            }
            $object = new \stdClass();
            $object->role='annotation';

            $objectText = new \stdClass();
            $objectText->role='annotationText';
            $result->columnNames = array('controller','Memoria usada (Mb)',$object,$objectText, 'Memoria Máxima (Mb)',$object,$objectText,);
            $result->datas = $data;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se ha podido realizar la peticion';
        }
        return response()->json($result);
    }

    /*
    * Top 10 method que mas memoria utiliza en promedio
    * Estadisticas basada en la utilizacion de memoria
    * en promedio que utiliza una method en tiempo
    * determinado
    * @public
    * @Autor Cristobal Bravo
    * @return response
    * @params request
    * En caso de que la peticion sea exitosa code 200
    * En caso contrario code 400
    */
    public function topMemoryByMethod(Request $request){
        $result = new \stdClass();
        $result->code = 200;
        $start=1605300341;
        $tiempo=$request->tiempo;
        $stop = strtotime($tiempo,$start);
        $nombre= "method";
        try{
            $tops=$this->almacenarRedis($nombre, $stop);
            $data = Array();
            $i=0;
            foreach($tops as $top) {
                $data[$i] =array(strVal($i+1),$top->memory_usage,$top->memory_usage,$top->method,$top->memory_peak,$top->memory_peak,$top->method,);
                $i++;
            }
            $object = new \stdClass();
            $object->role='annotation';

            $objectText = new \stdClass();
            $objectText->role='annotationText';
            $result->columnNames = array('Method','Memoria usada (Mb)',$object,$objectText, 'Memoria Máxima (Mb)',$object,$objectText,);
            $result->datas = $data;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se ha podido realizar la peticion';
        }
        return response()->json($result);
    }

    private function ObtenerTopMemoria($nombre, $stop){
        $tops = SystemLog::raw(function($collection) use ($stop, $nombre){
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
                            $nombre => '$'.$nombre
                        ],
                        'memory_peak'=>[
                            '$avg'=>'$memory_peak'
                        ],
                        'memory_usage'=>[
                            '$avg'=>'$memory_usage'
                        ]
                    ]
                ],
                ['$project' => [
                    'memory_usage' => '$memory_usage',
                    'memory_peak' => '$memory_peak',
                    $nombre=> '$_id.'.$nombre,
                    'promedio'=> ['$let' =>[
                                    'vars'=>[
                                        'cantidad' => [
                                            '$sum' => ['$memory_usage','$memory_peak']
                                            ]
                                        ],
                                        'in'=> ['$divide'=>['$$cantidad', 2]]
                                    ]
                                ],
                        '_id' => 0]
                ],
                ['$sort' => ['promedio' => -1]],
                ['$limit'=>10],
                ]);
            });
        return $tops;

    }

    private function almacenarRedis($nombre, $stop){

        if(Cache::has('estadisticas_Memoria_'.$nombre.'tiempo'.$stop)){
            $tops = Cache::get('estadisticas_Memoria_'.$nombre.'tiempo'.$stop);
        }else{
            $tops = $this->ObtenerTopMemoria($nombre,$stop);
            Cache::put('estadisticas_Memoria_'.$nombre.'tiempo'.$stop, $tops,config('global.REDIS_TIMER_12HRS'));
        }

        return $tops;

    }
}
