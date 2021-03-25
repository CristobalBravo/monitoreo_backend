<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Mail\FirstPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller{

    public function registro(Request $request){

        $result = new \stdClass();
        $result->code = 200;
        $validator = Validator::make($request->all(), [
            'nombre' => 'bail|required|string',
            'apellido' => 'bail|required|string',
            'rut' => 'bail|required|unique:mongodb.users|string',
            'perfil' => 'bail|required|string',
            'email' => 'bail|required|email'
        ]);
        if ($validator->fails()) {
            $result->code = 400;
            $result->status = 'error';
            $result->message = 'No cumple con las precondiciones de los campos';
            $result->errors = $validator->errors();
            return response()->json($result);
        }

        try{
            $user = new User();
            $user->nombre = $request->nombre;
            $user->apellido= $request->apellido;
            $user->rut= $request->rut;
            $user->perfil = $request->perfil;
            $user->email = $request->email;
            //ultimos 4 digitos del rut antes dv
            $last_4digits=substr($request->rut, 4, -1);
            //cifrado de password
            $password=hash('sha256',$last_4digits);
            $user->password=$password;
            $user->save();
            $result->user=$user;
            $result->code = 200;
            $result->message = 'usuario creado con exito';
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se ha podido crear el usuario';
        }
        return response()->json($result);
    }

    public function editar(Request $request){

        $result = new \stdClass();
        $result->code = 200;
        if($request->_id == ''){
            $result->code=400;
            $result->message = "Debes seleccionar un id de usuario para editar";
            return response()->json($result);
        }

        try{
            $user = User::findOrfail($request->_id);
            $user->nombre = $request->nombre;
            $user->apellido= $request->apellido;
            $user->rut= $request->rut;
            $user->perfil = $request->perfil;
            $user->email = $request->email;
            $user->save();
            $result->user=$user;
            $result->code = 200;
            $result->message = 'usuario editado con exito';
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se encontro el id';
        }
        return response()->json($result);
    }

    public function eliminar(Request $request){

        $result = new \stdClass();
        $result->code = 200;
        if($request->_id == ''){
            $result->code=400;
            $result->message = "Debes seleccionar un id de usuario para eliminar";
            return response()->json($result);
        }

        try{
            $user = User::findOrfail($request->_id);
            $user->delete();
            $result->code = 200;
            $result->message = 'usuario eliminado con exito';
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se encontro el id';
        }
        return response()->json($result);
    }

    public function listar(Request $request){
        $result = new \stdClass();
        $result->code = 200;
        try{
            $usuarios= User::all();
            $result->usuarios= $usuarios;
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se pudo realizar la peticion';
        }
        return response()->json($result);
    }

    public function buscar(Request $request){

        $result = new \stdClass();
        $result->code = 200;
        if($request->_id == ''){
            $result->code=400;
            $result->message = "Debes seleccionar un id de usuario para buscar";
            return response()->json($result);
        }

        try{
            $user = User::findOrfail($request->_id);
            $result->user=$user;
            $result->code = 200;
            $result->message = 'usuario se encontro con exito';
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'No se encontro el id';
        }
        return response()->json($result);
    }

    public function login(Request $request){

        $jwtAuth = new JwtAuth();
        $result = new \stdClass();

        $validator = Validator::make($request->all(), [
            'rut' => 'bail|required|string',
            'password' => 'bail|required|string'
        ]);
        if ($validator->fails()) {
            $result->status = 'error';
            $result->message = 'No cumple con las precondiciones de los campos';
            $result->errors = $validator->errors();
        }else{
            $rut=$request->rut;
            $last_4digits=substr($request->rut, 4, -1);
            if($request->password== $last_4digits ){
                $user =  User::where('rut', $rut)
                                ->first();
                $code= rand();
                $details=[
                    'nombre'=> $user->nombre,
                    'apellido'=> $user->apellido,
                    'code'=>$code
                ];
                $user->code = $code;
                $user->save();
                Mail::to($user->email)->send(new FirstPassword($details));
                $result->user= $user;
                $result->code=300;
                $result->message="Correo Enviado Con Exito";
                return response()->json($result);
            }else{
                $pwd=hash('sha256',$request->password);
                if(is_array($jwtAuth->singup($rut, $pwd))){
                    $result->data=$jwtAuth->singup($rut, $pwd);
                }else{
                    $result->token=$jwtAuth->singup($rut, $pwd);
                }

                if(!empty($request->getToken)){
                    $result->user= $jwtAuth->singup($rut, $pwd, true);
                }
            }
        }
        return response()->json($result);
    }

    public function setPassword(Request $request){
        $result = new \stdClass();
        $code= $request->code;
        try{
            $user = User::findOrfail($request->_id);
            if($code != $user->code){
                $result->message='Codigo Incorrecto';
                $result->code = 400;
            }else{
                $pwd = hash('sha256',$request->password);
                $user->password=$pwd;
                $user->code='';
                $user->save();
                $result->code = 200;
                $result->message = 'Contraseña cambiada con éxito';
            }
        }catch (\Exception $e) {
            $result->code = 400;
            $result->mensaje= 'usuario no encontrado';
        }
        return response()->json($result);
    }
}
