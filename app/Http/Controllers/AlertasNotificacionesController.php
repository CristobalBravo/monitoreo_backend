<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Models\AlertasNotificaciones;
use App\Models\SystemLog;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use stdClass;

class AlertasNotificacionesController extends Controller{

    // para el envio de correo de notificacion son aquellas que son nuevas y detectadas en el momento
    // en el que se guarda y seran enviados segun su rol ya que las muy graves seran notificados a todos
    // y las leves seran notificados a los de menor rango se sabra segun el level del error

    // para la visualizacion de detalle se desplegara una ventana modal
    // con la informacion completa para esto se necesita rescatar el mensaje
    // para asi obtener la informacion completa a traves de una consulta y ver si se ignora o no
    // ademas antes de ejecutar al servicio de busqueda esta notificacion tendra
    // que ser editada como vista al momento de hacer click en ella


    public function obtenerDetalleNotificacion(Request $request){
        $result = new \stdClass();
        $mensajeBuscar = $request->mensaje;
        $detalle = SystemLog::where('msg', $mensajeBuscar)->first();
        $result->detalle = $detalle;
        $result->code =200;
        $result->mensaje = "mensaje obtenido con exito";
        return response()->json($result);
    }

    public function actualizarVisto(Request $request){
        date_default_timezone_set("America/Santiago");
        $result = new \stdClass();
        $id= $request->id;
        $notificacion = AlertasNotificaciones::find($id);
        $notificacion->fechaVista= date("d/m/Y G:i:s");
        $notificacion->vista=1;
        $notificacion->save();
        $result->notificacion = $notificacion;
        $result->code =200;
        $result->mensaje = "notificacion actualizada";
        return response()->json($result);
    }

    public function ignorar(Request $request){
        date_default_timezone_set("America/Santiago");
        $result = new \stdClass();
        $id= $request->id;
        $notificacion = AlertasNotificaciones::find($id);
        $notificacion->fechaIgnorado= date("d/m/Y G:i:s");
        $notificacion->ignorado=1;
        $notificacion->save();
        $result->notificacion = $notificacion;
        $result->code =200;
        $result->mensaje = "notificacion actualizada";
        return response()->json($result);
    }


    public function notificar(){
        $this->saveMessage();
        $cantidad = $this->getCantNotification();
        $listadoNotificaciones = $this->getListNotification();

        $result = new \stdClass();

        $result->cantidad =$cantidad;
        $result->notificaciones = $listadoNotificaciones;
        event(new NotificationEvent($result));

    }

    private function saveMessage(){

        date_default_timezone_set("America/Santiago");

        $alertasDB = new \stdClass();
        $alertasNotification = new \stdClass();
        $alertasNotificationNew = new \stdClass();
        $alertasDB=$this->getAllErrorDB();
        $alertasNotification=$this->getAllErrorNotification();

        if($alertasNotification->alertas->isEmpty()){
            foreach($alertasDB->alertas as $alert) {
                $notificacion =  new AlertasNotificaciones();
                $notificacion->mensaje = $alert->mensaje;
                $notificacion->level = $alert->level;
                $notificacion->detectado = date("d/m/Y G:i:s");
                $notificacion->ignorado = 0;
                $notificacion->inicio = 1;
                $notificacion->vista = 1;
                $notificacion->save();
            }
        }else{
            $mensaje=$this->getListMessage($alertasNotification);
            $alertasNotificationNew = $this->getAllNewAlert($mensaje);
            if($alertasNotificationNew->alertas->isNotEmpty()){
                foreach($alertasNotificationNew->alertas as $alert) {
                    $notificacion =  new AlertasNotificaciones();
                    $notificacion->mensaje = $alert->mensaje;
                    $notificacion->level = $alert->level;
                    $notificacion->detectado = date("d/m/Y G:i:s");
                    $notificacion->ignorado = 0;
                    $notificacion->inicio = 0;
                    $notificacion->vista = 0;
                    $notificacion->save();
                }
            }
        }
    }


    private function getAllErrorDB(){

        $result = new \stdClass();
        $alertas = SystemLog::raw(function($collection) {
            return $collection->aggregate([
                [
                    '$match' => [
                            'user_id' => ['$ne' => 0 ],
                            'level'   => ['$gte'=> 250]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'mensaje' => '$msg'
                        ],
                        'level'=>[
                            '$first'=>'$level'
                        ]
                    ]
                ],
                ['$project' => [
                    'level' => '$level',
                    'mensaje' => '$_id.mensaje',
                    '_id' => 0]
                ],
                ]);
            });
            $result->alertas=$alertas;
        return $result;
    }

    private function getAllErrorNotification(){
        $result = new \stdClass();
        $alertas = AlertasNotificaciones::raw(function($collection) {
            return $collection->aggregate([
                [
                    '$match' => [
                            'user_id' => ['$ne' => 0 ],
                            'level'   => ['$gte'=> 250]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'mensaje' => '$mensaje'
                        ],
                        'level'=>[
                            '$first'=>'$level'
                        ]
                    ]
                ],
                ['$project' => [
                    'level' => '$level',
                    'mensaje' => '$_id.mensaje',
                    '_id' => 0]
                ],
                ]);
            });
            $result->alertas=$alertas;
        return $result;

    }


    private function getAllNewAlert($mensaje){
        $result = new \stdClass();
        $alertas = SystemLog::raw(function($collection) use ($mensaje) {
            return $collection->aggregate([
                [
                    '$match' => [
                            'msg'=> ['$nin'=> $mensaje],
                            'user_id' => ['$ne' => 0 ],
                            'level'   => ['$gte'=> 250]

                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'msg' => '$msg'
                        ],
                        'level'=>[
                            '$first'=>'$level'
                        ]
                    ]
                ],
                ['$project' => [
                    'level' => '$level',
                    'mensaje' => '$_id.msg',
                    '_id' => 0]
                ],
                ]);
            });
            $result->alertas=$alertas;
        return $result;

    }


    public function getListMessage( $alertasNotification){
        $mensaje = Array();
        $i=0;

        foreach($alertasNotification->alertas as $alert) {
                $mensaje[$i] = $alert->mensaje;
                $i++;
        }

        return $mensaje;

    }

    private function getCantNotification(){
        $cantidad = AlertasNotificaciones::where('vista', 0)
                                          ->where('inicio', 0)
                                          ->count();
        return $cantidad;
    }

    private function getListNotification(){
        $listadoNotificaciones = AlertasNotificaciones::where('ignorado', 0)
                                                        ->orderBy('detectado', 'desc')
                                          ->get();
        return $listadoNotificaciones;
    }
}
