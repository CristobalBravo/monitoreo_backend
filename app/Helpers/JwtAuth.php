<?php
namespace App\Helpers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;

class JwtAuth{

    public $key;

    public function __construct(){
        $this->key = 'esto_es_una_clave_para_el_sistema_monitoreo_adecca-987654321';
    }

    public function singup($rut, $password, $getToken= null){

        $user =  User::where('rut', $rut)
                      ->where ('password', $password)
                      ->first();
        $signup=false;
        if(is_object($user)){
            $signup=true;
        }

        if($signup){
            $token = array(
                'sub' => $user->_id,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'rut'=> $user->rut,
                'perfil' => $user->perfil,
                'iat' => time(),
                'exp' => time()+ (7*24*60*60)

            );
            $jwt = JWT::encode($token,$this->key, 'HS256');
            $decode = JWT::decode($jwt, $this->key, ['HS256']);
            if(is_null($getToken)){
                $data = $jwt;
            }else{
                $data =  $decode;
            }
        }else{
            $data = array( 'status'=> 'error', 'message'=> 'Credenciales Invalidas');
        }
        return $data;
    }
}
