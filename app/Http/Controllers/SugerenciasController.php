<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionSugerencia;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SugerenciasController extends Controller {

    public function obtenerSugereciasMemoriaMaxima(Request $request){
        $result = new \stdClass();
        $dato="memory_peak";
        $nombre=$request->nombre;
        $start=1605300341;
        $tiempo=$request->tiempo;
        $stop = strtotime($tiempo,$start);
        $porcentajeTolerancia = $this->obtenerPorcentajeTolerancia($dato,$nombre);

        $promedioTolerado = $this->porcentajeTolerado($porcentajeTolerancia,$dato);

        $sugerencia = $this->almacenarEnRedis($dato, $promedioTolerado,$nombre,$stop);
        $result->porcentajeTolerancia = $porcentajeTolerancia;
        $result->promedioTolerado= $promedioTolerado;
        $result->code = 200;
        $result->message = 'Sugerencias obtenidas exitosamente';
        $result->sugerencia= $sugerencia;
        return response()->json($result);
    }
    public function obtenerSugereciasMemoriaUsada(Request $request){
        $result = new \stdClass();
        $dato="memory_usage";
        $nombre=$request->nombre;
        $start=1605300341;
        $tiempo=$request->tiempo;
        $stop = strtotime($tiempo,$start);
        $porcentajeTolerancia = $this->obtenerPorcentajeTolerancia($dato,$nombre);

        $promedioTolerado = $this->porcentajeTolerado($porcentajeTolerancia,$dato);

        $sugerencia = $this->almacenarEnRedis($dato, $promedioTolerado,$nombre,$stop);
        $result->porcentajeTolerancia = $porcentajeTolerancia;
        $result->promedioTolerado= $promedioTolerado;
        $result->code = 200;
        $result->message = 'Sugerencias obtenidas exitosamente';
        $result->sugerencia= $sugerencia;
        return response()->json($result);

    }
    public function obtenerSugereciasTiempoDeEjecucion(Request $request){
        $result = new \stdClass();
        $dato="execution_time";
        $nombre=$request->nombre;
        $start=1605300341;
        $tiempo=$request->tiempo;
        $stop = strtotime($tiempo,$start);
        $porcentajeTolerancia = $this->obtenerPorcentajeTolerancia($dato,$nombre);

        $promedioTolerado = $this->porcentajeTolerado($porcentajeTolerancia,$dato);

        $sugerencia = $this->almacenarEnRedis($dato, $promedioTolerado,$nombre,$stop);
        $result->porcentajeTolerancia = $porcentajeTolerancia;
        $result->promedioTolerado= $promedioTolerado;
        $result->code = 200;
        $result->message = 'Sugerencias obtenidas exitosamente';
        $result->sugerencia= $sugerencia;
        return response()->json($result);
    }

    private function obtenerPorcentajeTolerancia($dato, $nombreBuscar){
        $configuracionSugerencias = ConfiguracionSugerencia::where('nombre', $nombreBuscar)
                                                             ->first();
        $porcentajeTolerancia=$configuracionSugerencias[$dato];
        return $porcentajeTolerancia ;
    }

    private function porcentajeTolerado($porcentajeTolerancia, $dato){

        $promedio = SystemLog::raw(function($collection) use ($dato, $porcentajeTolerancia){
            return $collection->aggregate([
                    [
                        '$group' => [
                            '_id' => [
                                'null'
                            ],
                            'promedioTolerado'=>[
                                '$avg'=>'$'.$dato
                            ],
                        ]
                    ],

                    ['$project' => [
                        '_id'=>0,
                        'promedioTolerado' => '$promedioTolerado']
                    ],
                ]);
            });

        $promedio = $promedio->first();
        $promedioTolerado=($promedio['promedioTolerado']*(float)$porcentajeTolerancia)+$promedio['promedioTolerado'];
        return $promedioTolerado;
    }


    private function almacenarEnRedis($dato, $promedioTolerado,$nombre,$stop){
        if(Cache::has('sugerencias'.$dato.$nombre.$stop)){
            $sugerencia = Cache::get('sugerencias'.$dato.$nombre.$stop);
        }else{
            $sugerencia = $this->generacionSugerencia($dato, $promedioTolerado,$nombre,$stop);
            Cache::put('sugerencias'.$dato.$nombre.$stop, $sugerencia,config('global.REDIS_TIMER_12HRS'));
        }
        return $sugerencia;
    }
    private function generacionSugerencia($dato, $promedioTolerado,$nombre,$stop){
        $sugerencia = SystemLog::raw(function($collection) use ($dato, $promedioTolerado, $nombre, $stop){
            return $collection->aggregate([
                [
                    '$match' => [
                            'user_id' => ['$ne' => 0 ],
                            $dato   => ['$gt'=> $promedioTolerado],
                            'date'    => ['$gte'=> $stop]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            $nombre => '$'.$nombre
                        ],
                        'usuarios'=>[
                            '$addToSet'=>'$user_id'
                        ],
                        $nombre=>[
                            '$first'=>'$'.$nombre
                        ],
                        'prom_'.$dato=>[
                            '$avg'=>'$'.$dato
                        ],
                        'cantidad' => ['$sum' => 1]
                    ]
                ],
                ['$project' => [
                    'prom_'.$dato => '$prom_'.$dato,
                    'usuarios_afectados' => ['$cond'=>['if'=>['$isArray'=>'$usuarios'], 'then' =>['$size'=>'$usuarios'], 'else'=>0]],
                    'cantidad' => '$cantidad',
                        $nombre => '$'.$nombre,
                    '_id' => 0]
                ],
                ['$sort' => ['cantidad' => -1]]
                ]);
            });
        return $sugerencia;
    }

}
