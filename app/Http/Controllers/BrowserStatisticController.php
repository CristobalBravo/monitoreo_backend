<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\Logs_user;
use App\Models\SystemLog;
use Dotenv\Regex\Result;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class BrowserStatisticController extends Controller{
    /*
    * Top 10 navegadores que mas utiliza la plataforma
    * Obtiene los 10 navegadores que mas utilizan la plataforma
    * de forma ordena y decendente.
    * @Autor Cristobal Bravo
    * @access public
    * @return response
    * En caso de lograr la peticion code 200
    * En caso contrario code 400 con su respectivo mensaje
    */
    public function top(){
        $result = new \stdClass();
        $start=1605300341;
        $stop = strtotime("-1 week",$start);
        try{
            $total=$this->totalUsuario($stop, $start);
            $Browsers= $this->obtenerNavegadores();
            if(Cache::has('estadisticas_Navegador_Version_top_7days')){
                $data = Cache::get('estadisticas_Navegador_Version_top_7days');
            }else{
                $i=0;
                foreach($Browsers as $browser){
                    $cantidad =SystemLog::where('browser',$browser->browser)
                                        ->groupBy('user_id')
                                        ->whereBetween('date',array($stop, $start))
                                        ->get()
                                        ->count();
                    $data[$i]= (object)array('nombre'=>$browser->browser, 'cantidad' => $cantidad);
                    $i++;
                }
                usort($data, function($a, $b) {
                    return $a->cantidad > $b->cantidad ? -1 : 1;
                });
                Cache::put('estadisticas_Navegador_Version_top_7days', $data,config('global.REDIS_TIMER_12HRS'));
            }
            $result->code = 200;
            $result->data = $data;
            $result->total= $total;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'no se ha podido realizar la peticion';
        }
        return response()->json($result);
    }

    public function topByMonth(){

        $start=1605300341;
        $stop = strtotime("-4 week",$start);
         $result = new \stdClass();
         try{

            $total=$this->totalUsuario($stop, $start);
            $Browsers= $this->obtenerNavegadores();

            if(Cache::has('estadisticas_Navegador_Version_top_1Month')){
                $data = Cache::get('estadisticas_Navegador_Version_top_1Month');
            }else{
                $i=0;
                foreach($Browsers as $browser){
                    $cantidad =SystemLog::where('browser',$browser->browser)
                                        ->groupBy('user_id')
                                        ->whereBetween('date',array($stop, $start))
                                        ->get()
                                        ->count();
                    $data[$i]= (object)array('nombre'=>$browser->browser, 'cantidad' => $cantidad);
                    $i++;
                }
                usort($data, function($a, $b) {
                    return $a->cantidad > $b->cantidad ? -1 : 1;
                });
                Cache::put('estadisticas_Navegador_Version_top_1Month', $data,config('global.REDIS_TIMER_12HRS'));
            }
             $result->code = 200;
             $result->data = $data;
             $result->total= $total;
         }catch (\Exception $e) {
             $result->code = 400;
             $result->mensaje= 'no se ha podido realizar la peticion';
         }
         return response()->json($result);
     }
     public function topByYear(){

        $start=1605300341;
        $stop = strtotime("-1 year",$start);
         $result = new \stdClass();
         try{
            $total=$this->totalUsuario($stop, $start);
            $Browsers= $this->obtenerNavegadores();
            if(Cache::has('estadisticas_Navegador_Version_top_1year')){
                $data = Cache::get('estadisticas_Navegador_Version_top_1year');
            }else{
                $i=0;
                foreach($Browsers as $browser){
                    $cantidad =SystemLog::where('browser',$browser->browser)
                                        ->groupBy('user_id')
                                        ->whereBetween('date',array($stop, $start))
                                        ->get()
                                        ->count();
                    $data[$i]= (object)array('nombre'=>$browser->browser, 'cantidad' => $cantidad);
                    $i++;
                }
                usort($data, function($a, $b) {
                    return $a->cantidad > $b->cantidad ? -1 : 1;
                });
                Cache::put('estadisticas_Navegador_Version_top_1year', $data,config('global.REDIS_TIMER_12HRS'));
            }
             $result->code = 200;
             $result->data = $data;
             $result->total= $total;
         }catch (\Exception $e) {
             $result->code = 400;
             $result->mensaje= 'no se ha podido realizar la peticion';
         }
         return response()->json($result);
     }
    /*
    * Cantidad de utilizacion de los SO por los Usuarios
    * Obtiene la cantidad de uso por SO que realizan acciones
    * los Usuarios de Adecca
    * @Autor Cristobal Bravo
    * @access public
    * @return response
    * En caso de lograr la peticion code 200
    * En caso contrario code 400 con su respectivo mensaje
    */
    public function so(){
        $result = new \stdClass();
        $result->code=200;
        try{
            $sos = SystemLog::groupBy('platform')
                        ->get(['platform']);
            $i=0;
            foreach($sos as $so){
                $cantidad =SystemLog::where('platform',$so->platform)
                                    ->where('user_id','!=',0)
                                    ->groupBy('user_id')
                                // ->whereBetween('fecha',array($start, $stop))
                                    ->get()
                                    ->count();

                $data[$i]= array($so->platform, $cantidad);
                $i++;
            }
            $result->data=$data;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se ha podido realizar la peticion';
        }
        return response()->json($result);
    }
    /*
    * Cantidad de utilizacion de los Versiones de Navegadores por los Usuarios
    * Obtiene la cantidad de uso de las versiones de los navegadores que mas
    * utilizan los usuarios de la plataforma
    * @Autor Cristobal Bravo
    * @access public
    * @return response
    * En caso de lograr la peticion code 200
    * En caso contrario code 400 con su respectivo mensaje
    */
    public function browserVersion(Request $request){
        $result = new \stdClass();
        $result->code = 200;
        try{
            $browsers =  $this->obtenerNavegadores();
            $i=0;

            if(Cache::has('estadisticas_Navegador_Version')){
                $tops = Cache::get('estadisticas_Navegador_Version');
            }else{
                foreach($browsers as $browser) {
                    $browser= $browser->browser;
                $tops[$i] = SystemLog::raw(function ($collection) use ($browser){
                    return $collection->aggregate([
                         [
                                 '$match' => [
                                         'usuario_id' => ['$ne' => 0 ],
                                         'browser'=> $browser
                                 ]
                             ],
                             [
                                 '$group' => [
                                     '_id' => [
                                         'browser' => '$browser',
                                         'browser_version' => '$browser_version',

                                     ],
                                     'cantidad' => ['$sum' => 1]
                                 ]
                             ],
                             ['$project' => [
                                     'cantidad' => '$cantidad',
                                     'browser_version' => '$_id.browser_version',
                                     'browser' => '$_id.browser',
                                     '_id' => 0]
                             ],
                             ['$sort' =>['cantidad'=> -1]],
                             ['$limit'=>1]
                            ]);
                    });
                    $i++;
                }
                Cache::put('estadisticas_Navegador_Version', $tops,config('global.REDIS_TIMER_12HRS'));
            }
            $data = Array();
            $i=0;
            foreach($tops as $top) {
                foreach($top as $t) {
                    $data[$i] = new \stdClass();
                    $data[$i]->browser = $t->browser;
                    $data[$i]->browser_version = $t->browser_version;
                    $data[$i]->cantidad = $t->cantidad;
                    $i++;
                }
            }
            $result->data = $data;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se ha podido realizar la peticion';
        }
        return response()->json($result);
    }
    /*
    * Listado de Versiones Segun El Browser
    * Obtiene el listado de versiones segun el navegador
    * en una consulta doble ya que entregar lo mas ocupado
    * y lo menos ocupado
    * @Autor Cristobal Bravo
    * @access public
    * @return response
    * En caso de lograr la peticion code 200
    * En caso contrario code 400 con su respectivo mensaje
    */
    public function topVersionByBrowser(Request $request){
        $start=1605300341;
        $tiempo=$request->tiempo;
        $stop = strtotime($tiempo,$start);
        if(!empty($request->all())){
           $validate = Validator::make($request->all(),[
               'browser_name'=>'required'
           ]);
           if($validate->fails()){
               $data=[
                   'code'=>400,
                   'status'=>'error',
                   'mensaje'=>'el servicio no pudo traer los datos'
               ];
           }else{
            $browser_name= $request->browser_name;
            if(Cache::has('estadisticas_Navegador_Version_Browser_Asc_'.$browser_name.$tiempo)){
                $tops = Cache::get('estadisticas_Navegador_Version_Browser_Asc'.$browser_name.$tiempo);
            }else{
                $tops= SystemLog::raw(function ($collection) use ($browser_name, $stop){
                    return $collection->aggregate([
                            [
                                '$match' => [
                                        'user_id' => ['$ne' => 0 ],
                                        'date'    => ['$gte'=> $stop],
                                        'browser'=> $browser_name

                                    ]
                                ],
                                    [
                                    '$group' => [
                                            '_id' => [
                                            'browser' => '$browser',
                                            'browser_version' => '$browser_version',
                                            ],
                                            'cantidad' => ['$sum' => 1]
                                        ]
                                    ],
                                    ['$project' => [
                                            'cantidad' => '$cantidad',
                                            'browser_version' => '$_id.browser_version',
                                            'browser' => '$_id.browser',
                                            '_id' => 0]
                                    ],
                                ['$sort' =>['cantidad'=> -1]],
                            ['$limit'=>5]
                        ]);
                    });
                Cache::put('estadisticas_Navegador_Version_Browser_Asc'.$browser_name.$tiempo, $tops,config('global.REDIS_TIMER_12HRS'));
            }
            $asc = Array();
            $i=0;
            foreach($tops as $top) {
                $asc[$i] = new \stdClass();
                $asc[$i]->browser_version = $top->browser_version;
                $asc[$i]->cantidad = $top->cantidad;
                $i++;
            }

            if(Cache::has('estadisticas_Navegador_Version_Browser_desc'.$browser_name.$tiempo)){
                $descs = Cache::get('estadisticas_Navegador_Version_Browser_desc'.$browser_name.$tiempo);
            }else{
                $descs= SystemLog::raw(function ($collection) use ($browser_name, $stop){
                    return $collection->aggregate([
                         [
                             '$match' => [
                                     'user_id' => ['$ne' => 0 ],
                                     'browser'=> $browser_name,
                                     'date'    => ['$gte'=> $stop]
                                 ]
                             ],
                                 [
                                 '$group' => [
                                         '_id' => [
                                         'browser' => '$browser',
                                         'browser_version' => '$browser_version',
                                         ],
                                         'cantidad' => ['$sum' => 1]
                                     ]
                                 ],
                                 ['$project' => [
                                         'cantidad' => '$cantidad',
                                         'browser_version' => '$_id.browser_version',
                                         'browser' => '$_id.browser',
                                         '_id' => 0]
                                 ],
                             ['$sort' =>['cantidad'=> 1]],
                         ['$limit'=>5]
                     ]);
                 });
                Cache::put('estadisticas_Navegador_Version_Browser_desc'.$browser_name.$tiempo, $descs,config('global.REDIS_TIMER_12HRS'));
            }
         $desc = Array();
         $i=0;
         foreach($descs as $top) {
            $desc[$i] = new \stdClass();
            $desc[$i]->browser_version = $top->browser_version;
            $desc[$i]->cantidad = $top->cantidad;
            $i++;
         }
            $data=[
                'code'=>200,
                'ascendentes'=>$asc,
                'descendentes'=>$desc
            ];
           }
        }else{
            $data=[
                'code'=>400,
                'status'=> 'error',
                'mensaje'=>'No ha ingresado el nombre del navegador'];
        }
        return response()->json($data);
    }
    /*
    * Listado de Browser
    * Obtiene un listado de navegadores que se hayan
    * utilizado los usuarios de la plataforma
    * @Autor Cristobal Bravo
    * @access public
    * @return response
    * En caso de lograr la peticion code 200
    * En caso contrario code 400 con su respectivo mensaje
    */
    public function browserList(){
        $result = new \stdClass();
        $result->code = 200;
        try{
            $browsers =  $this->obtenerNavegadores();
            $i=0;
            foreach($browsers as $browser) {
                $data[$i] = new \stdClass();
                $data[$i]->browser_name = $browser->browser;
                $i++;
            }
            $result->browsers = $data;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se ha podido realizar la peticion';
        }
        return response()->json($result);
    }

    private function totalUsuario($stop, $start){
        $total = SystemLog::groupBy('user_id')
                            ->whereBetween('date',array($stop, $start))
                            ->get()
                            ->count();
        return $total;
    }

    private function obtenerNavegadores(){

        if(Cache::has('estadisticas_Navegador_Version_list')){
            $Browsers = Cache::get('estadisticas_Navegador_Version_list');
        }else{
            $Browsers = SystemLog::groupBy('browser')
                                ->get();
            Cache::put('estadisticas_Navegador_Version_list', $Browsers,config('global.REDIS_TIMER_12HRS'));
        }
        return $Browsers;
    }

    //Funcion Privada que permite guardar los datos en una nueva coleccion
    private function insercionDBMongoDB(){
        $logs = Logs::where('id','>',84710)->get();
        //$logs = Logs::all();
        foreach($logs as $log){
            $logs_user = new Logs_user();
            $agent = new Agent();
            $agent->setUserAgent($log->user_agent);
            $logs_user->id=$log->id;
            $logs_user->fecha= $log->fecha;
            $logs_user->usuario_id= $log->usuario_id;
            $logs_user->curso_id= $log->curso_id;
            $logs_user->modulo_id= $log->modulo_id;
            $logs_user->datos= $log->datos;
            $logs_user->log_accion_id= $log->log_accion_id;
            $logs_user->browser_name= $agent->browser();
            if($agent->isDesktop()){
                $logs_user->dispositive_type= 'Desktop';
            }else{
                if($agent->isMobile()){
                    $logs_user->dispositive_type= 'Mobile';
                }else{
                    if($agent->isTablet()){
                        $logs_user->dispositive_type= 'Tablet';
                    }
                }
            }
            $logs_user->ip='Por Definir';
            $logs_user->save();
        }
    }
}
