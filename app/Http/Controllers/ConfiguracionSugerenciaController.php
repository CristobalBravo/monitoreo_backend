<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionSugerencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function React\Promise\Stream\first;

class ConfiguracionSugerenciaController extends Controller{


    public function update(Request $request){

        $result = new \stdClass();
        $validator = Validator::make($request->all(), [
            'nombre' => 'bail|required|string']);

        if ($validator->fails()) {
            $result->status = 'error';
            $result->message = 'No cumple con las precondiciones de los campos';
            $result->errors = $validator->errors();
            return response()->json($result);
        }

        try{
            $configuracionSugerencias = ConfiguracionSugerencia::where('nombre', $request->nombre)
                                                                ->first();

            if(is_null($configuracionSugerencias)){
                $configuracionSugerencias= new ConfiguracionSugerencia();
                $configuracionSugerencias->nombre= $request->nombre;
                $configuracionSugerencias->memory_usage= 0.0;
                $configuracionSugerencias->execution_time= 0.0;
                $configuracionSugerencias->memory_peak = 0.0;
                $configuracionSugerencias->save();
            }

            $configuracionSugerencias->memory_usage= $request->memory_usage;
            $configuracionSugerencias->execution_time= $request->execution_time;
            $configuracionSugerencias->memory_peak = $request->memory_peak;
            $configuracionSugerencias->save();
            $result->configuracionSugerencias=$configuracionSugerencias;
            $result->code = 200;
            $result->message = 'Configuracion editada con exito';
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se ha podido editar la configuracion';
        }
        return response()->json($result);
    }

    public function search(Request $request){
        $result = new \stdClass();
        $validator = Validator::make($request->all(), [
            'nombre' => 'bail|required|string']);

        if ($validator->fails()) {
            $result->status = 'error';
            $result->message = 'No cumple con las precondiciones de los campos';
            $result->errors = $validator->errors();
            return response()->json($result);
        }

        $configuracionSugerencias = ConfiguracionSugerencia::where('nombre', $request->nombre)
                                                                ->first();
        if(is_null($configuracionSugerencias)){
            $result->code = 400;
            $result->mensaje= 'No se a creado la configuracion';
        }else{
            $result->code = 200;
            $result->data=$configuracionSugerencias;
            $result->message = 'Configuracion encontrada con exito';
        }
        return response()->json($result);
    }
}
