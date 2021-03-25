<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logs_user;

class ModuloStatisticController extends Controller{
    /*
    * Top 10 tipo modulo que mas utiliza la plataforma
    * Obtiene los 10 tipo modulo que mas utilizan
    * la plataforma de forma ordena y decendente.
    * @Autor Cristobal Bravo
    * @access public
    * @return response
    * En caso de lograr la peticion code 200
    * En caso contrario code 400 con su respectivo mensaje
    */
    public function moduloTypeTop(){
        $result = new \stdClass();
        $result->code = 200;
        try{
            $type_modules=Logs_user::groupBy('tipo_modulo')
                                    ->get();
            $i=0;
            foreach($type_modules as $type_module){
                $cantidad =Logs_user::where('tipo_modulo',$type_module->tipo_modulo)
                                    ->groupBy('usuario_id')
                                //  ->whereBetween('fecha',array($start, $stop))
                                    ->get()
                                    ->count();
                $data[$i]= (object)array('tipo_modulo'=>$type_module->tipo_modulo, 'cantidad' => $cantidad);
                $i++;
            }
            usort($data, function($a, $b) {
                return $a->cantidad > $b->cantidad ? -1 : 1;
            });
            $result->data = $data;
        }catch(\Exception $e) {
            $result->code = 400;
            $result->mensaje = 'No se ha podido realizar la peticion';
        }
        return response()->json($result);
    }
}
