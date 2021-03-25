<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ExecutionTimeStatisticsController extends Controller{

    /*
    * Top 10 Uri con mayor tiempo de ejecucion
    * Estadisticas en base al tiempo de ejecucion que tiene
    * una uri el cual entregara el top 10 que mas tiempo de
    * ejecucion tome
    * @public
    * @Autor Cristobal Bravo
    * @return response
    * En caso que la peticion sea exitosa code 200
    * en caso lo contrario code 400
    */
    public function topExecutionTimeByUri(Request $request){
        $result = new \stdClass();
        $result->code = 200;
        $nombre= "uri";
        $start=1605300341;
        $tiempo=$request->tiempo;
        $stop = strtotime($tiempo,$start);
        try{
            $tops = $this->almacenarRedis($nombre, $stop);
            $data = Array();
            $i=0;
            foreach($tops as $top) {
                $data[$i] = array(strVal($i+1),$top->promedio,$top->promedio,$top->uri);
                $i++;
            }
            $object = new \stdClass();
            $object->role='annotation';

            $objectText = new \stdClass();
            $objectText->role='annotationText';

            $result->columnNames = array('URI','Promedio (ms)',$object,$objectText);
            $result->datas = $data;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se ha podido realizar la peticion';
        }
        return response()->json($result);
    }

    /*
    * Top 10 method con mayor tiempo de ejecucion
    * Estadisticas en base al tiempo de ejecucion que tiene
    * una method el cual entregara el top 10 que mas tiempo de
    * ejecucion tome
    * @public
    * @Autor Cristobal Bravo
    * @return response
    * En caso que la peticion sea exitosa code 200
    * en caso lo contrario code 400
    */
    public function topExecutionTimeByMethod(Request $request){
        $result = new \stdClass();
        $result->code = 200;
        $nombre= "method";
        $start=1605300341;
        $tiempo=$request->tiempo;
        $stop = strtotime($tiempo,$start);
        $object = new \stdClass();
        $object->role='annotation';

        $objectText = new \stdClass();
        $objectText->role='annotationText';

        try{
            $tops = $this->almacenarRedis($nombre, $stop);
            $data = Array();
            $i=0;
            foreach($tops as $top) {
                $data[$i] =array(strVal($i+1),$top->promedio,$top->promedio,$top->method);
                $i++;
            }

            $result->columnNames = array('Method','Promedio (ms)',$object,$objectText);
            $result->datas = $data;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se ha podido realizar la peticion';
        }
        return response()->json($result);
    }

    /*
    * Top 10 controller con mayor tiempo de ejecucion
    * Estadisticas en base al tiempo de ejecucion que tiene
    * una controller el cual entregara el top 10 que mas tiempo de
    * ejecucion tome
    * @public
    * @Autor Cristobal Bravo
    * @return response
    * En caso que la peticion sea exitosa code 200
    * en caso lo contrario code 400
    */
    public function topExecutionTimeByController(Request $request){
        $result = new \stdClass();
        $result->code = 200;
        $nombre= "controller";
        $start=1605300341;
        $tiempo=$request->tiempo;
        $stop = strtotime($tiempo,$start);

        try{
            $tops = $this->almacenarRedis($nombre, $stop);
            $data = Array();
            $i=0;
            foreach($tops as $top) {
                $data[$i] =array(strval($i+1),$top->promedio,$top->promedio,$top->controller);
                $i++;
            }

            $object = new \stdClass();
            $object->role='annotation';
            $object->type='number';

            $objectText = new \stdClass();
            $objectText->role='annotationText';
            $result->columnNames = array('Controller','Promedio (ms)', $object,$objectText);
            $result->datas = $data;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se ha podido realizar la peticion';
        }
        return response()->json($result);
    }

    private function obtenerTopExecutionTime($nombre,$stop){
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
                        'promedio'=>[
                            '$avg'=>'$execution_time'
                        ],
                    ]
                ],
                ['$project' => [
                    'promedio' => '$promedio',
                    $nombre=> '$_id.'.$nombre,
                        '_id' => 0]
                ],
                ['$sort' => ['promedio' => -1]],
                ['$limit'=>10],
                ]);
            });

        return $tops;
    }

    private function almacenarRedis($nombre, $stop){

        if(Cache::has('estadisticas_tiempoEjecucion_'.$nombre.'tiempo'.$stop)){
            $tops = Cache::get('estadisticas_tiempoEjecucion_'.$nombre.'tiempo'.$stop);
        }else{
            $tops = $this->obtenerTopExecutionTime($nombre,$stop);
            Cache::put('estadisticas_tiempoEjecucion_'.$nombre.'tiempo'.$stop, $tops,config('global.REDIS_TIMER_12HRS'));
        }

        return $tops;

    }
}
