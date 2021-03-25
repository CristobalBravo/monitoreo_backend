<?php

namespace App\Http\Controllers;

use App\Events\DonutsEvent;
use App\Events\MessageEvent;
use App\Models\Logs;
use App\Models\Logs_user;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use League\CommonMark\Extension\Table\Table;
use MongoDB\BSON\Regex;

class UserStatisticController extends Controller
{
    public function ingresar(Request $request){
            $result = new \stdClass();
            $now = new \DateTime('now');
            $log                = new Logs_user();
            $log->fecha         = $now->format('Y-m-d H:i:s');
            $log->ip            = $request->ip;
            $log->user_agent    = $request->user_agent;
            $log->usuario_id    = $request->usuario_id;
           // $log->log_accion_id = $request->log_accion_id;
            $log->save();
            $this->dispositivo();
            $this->userActive();
            $result->code = 200;
            $result->message = 'usuario Ingresado Con Exito';
            return response()->json($result);
    }

    private function userActive(){
        $user_conect = Logs_user::count();
        event(new MessageEvent($user_conect));
    }

    /*
    * Estadisticas por dispositivo
    * Metodo que retorna las estadisticas de uso
    * por dispositivo segun el usuario
    * @autor Cristobal Bravo
    * @access public
    * @return response
    * En caso de una respuesta positiva code 200
    * En caso de una respuesta negativa code 400
    */
    public function dispositivo(){

        $result = new \stdClass();


            if(Cache::has('estadisticas_usuario_dispositivo')){
                $result = Cache::get('estadisticas_usuario_dispositivo');
            }else{
                $dispositives = SystemLog::groupBy('device_type')
                                         ->get();
                foreach($dispositives as $dispositive){
                $nombre = $dispositive->device_type;
                $result->$nombre= SystemLog::where('device_type', $nombre)
                    ->groupBy('user_id')
                    ->get()
                    ->count();
                }
                Cache::put('estadisticas_usuario_dispositivo', $result,config('global.REDIS_TIMER_12HRS'));
            }
            $result->code=200;

        event(new DonutsEvent($result));
        return response()->json($result);
    }

    public function userByDate(Request $request){
        $inicio = $request->fecha;
        $fin= $request->fecha;
        $inicio.' 00:00:00';
        $fin.' 23:59:59';
        $logs_user= new Logs_user();
        $userbyDate = $logs_user
                        ->whereDate('fecha', '>', $inicio.' 00:00:00')
                        ->whereDate('fecha', '<=', $fin.' 23:59:59')
                        ->count();
        $result = new \stdClass();
        $result->code = 200;
        $result->cantidad = $userbyDate;
        return response()->json($result);

    }

    public function bySeventDay(Request $request){

        $result = new \stdClass();
        $systemLogs= new SystemLog();
        $day=7;
        $start=1605300341;
        if(Cache::has('bySeventDay')){
            $arrayFecha = Cache::get('bySeventDay');
        }else{
            for($i=0;$i<$day;$i++){
                $date_past = strtotime("- 1 days",$start);
                $userbyDate= $systemLogs->whereBetween('date', [$date_past,$start])
                                        ->count();
                $arrayFecha[$i] = array(date("d-m-Y",$start), $userbyDate);
                $start= $date_past;
            }
            Cache::put('bySeventDay', $arrayFecha,config('global.REDIS_TIMER_12HRS'));
        }
        $result->code=200;
        $result->data=$arrayFecha;
        //return view('consulta')->with('data',$arrayFecha);
        return response()->json($result);
    }


    public function byMonth(Request $request){

        $result = new \stdClass();
        $systemLogs= new SystemLog();
        $weeks=4;
        $start=1605300341;
        if(Cache::has('byMonth')){
            $arrayFecha = Cache::get('byMonth');
        }else{
            for($i=0;$i<$weeks;$i++){
                $date_past = strtotime("-1 week",$start);
                $userbyDate= $systemLogs->whereBetween('date', [$date_past,$start])
                                        ->count();
                $arrayFecha[$i] = array(date("d-m-Y",$start), $userbyDate);
                $start= $date_past;
            }
            Cache::put('byMonth', $arrayFecha,config('global.REDIS_TIMER_12HRS'));
        }
        $result->code=200;
        $result->data=$arrayFecha;
        //return view('consulta')->with('data',$arrayFecha);
        return response()->json($result);
    }

    public function byYear(Request $request){
        setlocale(LC_TIME, "spanish");
        $result = new \stdClass();
        $systemLogs= new SystemLog();
        $months=12;
        $start=1605300341;
        if(Cache::has('byYear')){
            $arrayFecha = Cache::get('byYear');
        }else{
            for($i=0;$i<$months;$i++){
                $date_past = strtotime("-1 month",$start);
                $userbyDate= $systemLogs->whereBetween('date', [$date_past,$start])
                                        ->count();
                $mes=date("M",$start);
                $mes_espaniol=strftime("%B" , strtotime($mes));
                $arrayFecha[$i] = array($mes_espaniol, $userbyDate);
                $start= $date_past;
            }
            Cache::put('byYear', $arrayFecha,config('global.REDIS_TIMER_12HRS'));
        }
        $result->code=200;
        $result->data=$arrayFecha;
        //return view('consulta')->with('data',$arrayFecha);
        return response()->json($result);
    }
    /*
    * Top 10 usuarios que mas utiliza la plataforma
    * Obtiene los 10 usuarios que mas utilizan la plataforma
    * de forma ordena y decendente.
    * @Autor Cristobal Bravo
    * @access public
    * @return response
    * En caso de lograr la peticion code 200
    * En caso contrario code 400
    */
    public function topUsers(){
        $result = new \stdClass();
        $result->code = 200;
        $start=1605300341;
        $stop = strtotime("-7 day",$start);
        try{
            $tops=$this->almacenarRedis($stop);
            $data = Array();
            $i=0;
            foreach($tops as $top) {
                $data[$i] = new \stdClass();
                $data[$i]->id = $top->user_id;
                $data[$i]->cantidad = $top->cantidad;
                $i++;
            }
            $result->data = $data;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'no se ha podido realizar la peticion';
        }
        return response()->json($result);
    }

    public function topUsersByMonth(){
        $result = new \stdClass();
        $result->code = 200;
        $start=1605300341;
        $stop = strtotime("-1 month",$start);
        try{
            $tops=$this->almacenarRedis($stop);
            $data = Array();
            $i=0;
            foreach($tops as $top) {
                $data[$i] = new \stdClass();
                $data[$i]->id = $top->user_id;
                $data[$i]->cantidad = $top->cantidad;
                $i++;
            }
            $result->data = $data;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'no se ha podido realizar la peticion';
        }
        return response()->json($result);
    }
    public function topUsersByYear(){
        $result = new \stdClass();
        $result->code = 200;
        $start=1605300341;
        $stop = strtotime("-1 year",$start);
        try{
            $tops=$this->almacenarRedis($stop);
            $data = Array();
            $i=0;
            foreach($tops as $top) {
                $data[$i] = new \stdClass();
                $data[$i]->id = $top->user_id;
                $data[$i]->cantidad = $top->cantidad;
                $i++;
            }
            $result->data = $data;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'no se ha podido realizar la peticion';
        }
        return response()->json($result);
    }
    private function obtenerTopsUsuario($stop){
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
                                'user_id' => '$user_id'
                            ],
                            'cantidad' => ['$sum' => 1]
                        ]
                    ],
                    ['$project' => [
                            'cantidad' => '$cantidad',
                            'user_id' => '$_id.user_id',
                            '_id' => 0]
                    ],
                    ['$sort' => ['cantidad' => -1]
                    ],
                    ['$limit'=>10]

            ]);
        });
        return $tops;
    }

    private function almacenarRedis($stop){
        if(Cache::has('estadisticas_usuario_top'.'tiempo'.$stop)){
            $tops = Cache::get('estadisticas_usuario_top'.'tiempo'.$stop);
        }else{
            $tops = $this->obtenerTopsUsuario($stop);
            Cache::put('estadisticas_usuario_top'.'tiempo'.$stop, $tops,config('global.REDIS_TIMER_12HRS'));
        }

        return $tops;
    }

    public function pruebaMongoDB(){
        $result = new \stdClass();
        $cantidad_json = SystemLog::where('is_ajax', "0")
                            ->count();
         $cantidad_number = SystemLog::where('is_ajax', 0)
                            ->count();
        $result->cantidad_ajax_json=$cantidad_json;
        $result->cantidad_ajax_number=$cantidad_number;
        return response()->json($result);
    }
}
