<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;

class AlertasController extends Controller
{
    public function getAllAlertsByDB(){
        $result = new \stdClass();
        try{
            $alertas = SystemLog::raw(function($collection) {
                return $collection->aggregate([
                    [
                        '$match' => [
                                'user_id' => ['$ne' => 0 ],
                                'level'   => ['$gte'=> 250],
                                'platform'   => 'mysql'
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => [
                                'msg' => '$msg'
                            ],
                            'usuarios'=>[
                                '$addToSet'=>'$user_id'
                            ],
                            'mensaje'=>[
                                '$first'=>'$msg'
                            ],
                            'err_number'=>[
                                '$first'=>'$err_number'
                            ],
                            'sql'=>[
                                '$first'=>'$sql'
                            ],
                            'platform'=>[
                                '$first'=>'$platform'
                            ],
                            'version'=>[
                                '$first'=>'$version'
                            ],
                            'level'=>[
                                '$first'=>'$level'
                            ],
                            'cantidad' => ['$sum' => 1]
                        ]
                    ],
                    // [
                    //     '$group' => [
                    //         '_id' => [
                    //             'usuario' => '$usuario'
                    //         ],
                    //         'err_number'=>[
                    //             '$first'=>'$err_number'
                    //         ],
                    //         'sql'=>[
                    //             '$first'=>'$sql'
                    //         ],
                    //         'cantidad' =>[
                    //             '$first'=>'$cantidad'
                    //         ],
                    //         'platform'=>[
                    //             '$first'=>'$platform'
                    //         ],
                    //         'mensaje'=>[
                    //             '$first'=>'$mensaje'
                    //         ],
                    //         'version'=>[
                    //             '$first'=>'$version'
                    //         ],
                    //         'usuarios_afectados' => ['$sum' => 1]
                    //     ]
                    // ],
                    ['$project' => [
                        'level' => '$level',
                        'mensaje' => '$mensaje',
                        'err_number' => '$err_number',
                        'sql' => '$sql',
                        'platform' => '$platform',
                        'version' => '$version',
                        'usuarios_afectados' => ['$cond'=>['if'=>['$isArray'=>'$usuarios'], 'then' =>['$size'=>'$usuarios'], 'else'=>0]],
                        'cantidad' => '$cantidad',
                        '_id' => 0]
                    ],
                    ['$sort' => ['cantidad' => -1]]
                    ]);
                });
                $result->code = 200;
                $result->message = 'Alertas obtenidas exitosamente';
                $result->alertas= $alertas;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se ha podido realizar la peticion';
        }

        return response()->json($result);

    }

    public function getAllAlertsByPHP(Request $request){
        $result = new \stdClass();
        try{
            $alertas = SystemLog::raw(function($collection) {
                return $collection->aggregate([
                    [
                        '$match' => [
                                'user_id' => ['$ne' => 0 ],
                                'level'   => ['$gte'=> 250],
                                'platform'   => 'PHP'
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => [
                                'msg' => '$msg'
                            ],
                            'usuarios'=>[
                                '$addToSet'=>'$user_id'
                            ],
                            'mensaje'=>[
                                '$first'=>'$msg'
                            ],
                            'err_number'=>[
                                '$first'=>'$err_number'
                            ],
                            'file'=>[
                                '$first'=>'$file'
                            ],

                            'line'=>[
                                '$first'=>'$line'
                            ],
                            'level'=>[
                                '$first'=>'$level'
                            ],
                            'platform'=>[
                                '$first'=>'$platform'
                            ],
                            'version'=>[
                                '$first'=>'$version'
                            ],
                            'cantidad' => ['$sum' => 1]
                        ]
                    ],
                    // [
                    //     '$group' => [
                    //         '_id' => [
                    //             'usuario' => '$usuario'
                    //         ],
                    //         'err_number'=>[
                    //             '$first'=>'$err_number'
                    //         ],
                    //         'sql'=>[
                    //             '$first'=>'$sql'
                    //         ],
                    //         'cantidad' =>[
                    //             '$first'=>'$cantidad'
                    //         ],
                    //         'platform'=>[
                    //             '$first'=>'$platform'
                    //         ],
                    //         'mensaje'=>[
                    //             '$first'=>'$mensaje'
                    //         ],
                    //         'version'=>[
                    //             '$first'=>'$version'
                    //         ],
                    //         'usuarios_afectados' => ['$sum' => 1]
                    //     ]
                    // ],
                    ['$project' => [
                        'level' => '$level',
                        'mensaje' => '$mensaje',
                        'line' => '$line',
                        'file' => '$file',
                        'err_number' => '$err_number',
                        'platform' => '$platform',
                        'version' => '$version',
                        'usuarios_afectados' => ['$cond'=>['if'=>['$isArray'=>'$usuarios'], 'then' =>['$size'=>'$usuarios'], 'else'=>0]],
                        'cantidad' => '$cantidad',
                        '_id' => 0]
                    ],
                    ['$sort' => ['cantidad' => -1]],
                    ['$limit' => 10],
                    ]);
                });
                $result->code = 200;
                $result->message = 'Alertas obtenidas exitosamente';
                $result->alertas= $alertas;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se ha podido realizar la peticion';
        }


        return response()->json($result);

    }

    public function getListPlatforms(){
        $result = new \stdClass();
        $result->code = 200;
        $platforms = $this->getAllPlatforms();
        $i=0;
        foreach($platforms as $platform) {
            $data[$i] = new \stdClass();
            $data[$i]->platform = $platform->platform;
            $i++;
        }
        $result->platforms = $data;
        return response()->json($result);
    }

    public function obtenerCantidadPorPlataformas(){
        $result = new \stdClass();
        try{
            $platforms = $this->getAllPlatforms();
            foreach($platforms as $platform){
            $nombre = $platform->platform;
            $result->$nombre= SystemLog::where('platform', $nombre)
                              ->where('user_id','!=', 0)
                              ->groupBy('msg')
                              ->get()
                              ->count();
            }
            $result->mensaje= 'Cantidades obtenidas con éxito';
            $result->code=200;
        } catch (\Exception $e) {
            $result->mensaje= 'No se ha podido realizar la peticion';
            $result->code = 400;
        }
        return response()->json($result);
    }

    public function obtenerCantidadPorCode(){
        $result = new \stdClass();
        try{
            $tops = SystemLog::raw(function($collection) {
                return $collection->aggregate([
                    [
                        '$match' => [
                                'user_id' => ['$ne' => 0 ],
                                'level'   => ['$gte'=> 250],
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => [
                                'err_number' => '$err_number'
                            ],
                            'cantidad' => ['$sum' => 1]
                        ]
                    ],
                    ['$project' => [
                        'err_number' => '$_id.err_number',
                        'cantidad' => '$cantidad',
                            '_id' => 0]
                    ],
                    ['$sort' => ['cantidad' => -1]],
                    ['$limit'=>5],
                    ]);
                });
            $data = Array();
            $i=0;
            foreach($tops as $top) {
                $data[$i] =array($top->err_number,$top->cantidad);
                $i++;
            }
            $result->columnNames = array('code','cantidad');
            $result->mensaje= 'Cantidades obtenidas con éxito';
            $result->code=200;
            $result->datas = $data;
        } catch (\Exception $e) {
            $result->mensaje= 'No se ha podido realizar la peticion';
            $result->code = 400;
        }
        return response()->json($result);
    }

    public function buscarAlertaPorMensajeError(Request $request){
        $mensaje=$request->mensaje;
        $result = new \stdClass();
        try{
            $error = SystemLog::raw(function($collection) use ($mensaje) {
                return $collection->aggregate([
                    [
                        '$match' => [
                                'msg'   => $mensaje
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => [
                                'msg' => '$msg'
                            ],
                            'usuarios'=>[
                                '$addToSet'=>'$user_id'
                            ],
                            'mensaje'=>[
                                '$first'=>'$msg'
                            ],
                            'err_number'=>[
                                '$first'=>'$err_number'
                            ],
                            'sql'=>[
                                '$first'=>'$sql'
                            ],
                            'file'=>[
                                '$first'=>'$file'
                            ],

                            'line'=>[
                                '$first'=>'$line'
                            ],
                            'level'=>[
                                '$first'=>'$level'
                            ],
                            'platform'=>[
                                '$first'=>'$platform'
                            ],
                            'version'=>[
                                '$first'=>'$version'
                            ],
                            'cantidad' => ['$sum' => 1]
                        ]
                    ],
                    // [
                    //     '$group' => [
                    //         '_id' => [
                    //             'usuario' => '$usuario'
                    //         ],
                    //         'err_number'=>[
                    //             '$first'=>'$err_number'
                    //         ],
                    //         'sql'=>[
                    //             '$first'=>'$sql'
                    //         ],
                    //         'cantidad' =>[
                    //             '$first'=>'$cantidad'
                    //         ],
                    //         'platform'=>[
                    //             '$first'=>'$platform'
                    //         ],
                    //         'mensaje'=>[
                    //             '$first'=>'$mensaje'
                    //         ],
                    //         'version'=>[
                    //             '$first'=>'$version'
                    //         ],
                    //         'usuarios_afectados' => ['$sum' => 1]
                    //     ]
                    // ],
                    ['$project' => [
                        'level' => '$level',
                        'mensaje' => '$mensaje',
                        'line' => '$line',
                        'file' => '$file',
                        'err_number' => '$err_number',
                        'platform' => '$platform',
                        'version' => '$version',
                        'usuarios_afectados' => ['$cond'=>['if'=>['$isArray'=>'$usuarios'], 'then' =>['$size'=>'$usuarios'], 'else'=>0]],
                        'cantidad' => '$cantidad',
                        '_id' => 0]
                    ],
                    ['$sort' => ['cantidad' => -1]],
                    ['$limit' => 10],
                    ]);
                });
                $result->code = 200;
                $result->message = 'Alertas obtenidas exitosamente';
                $result->datas = $error;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se ha podido realizar la peticion';
        }
        return response()->json($result);
    }

    private function getAllPlatforms(){
        $platforms = SystemLog::where('user_id', '!=', 0)
        ->where('level', '>=', 250)
        ->groupBy('platform')
        ->get();
        return $platforms;
    }

}
