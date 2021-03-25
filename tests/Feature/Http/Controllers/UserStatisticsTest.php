<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserStatisticsTest extends TestCase
{
    /**
     * Prueba basada en las estadististicas de los
     * del uso de los dispositivo segun los usuarios,
     * comprueba que el codigo del respuesta sea 200
     * en caso de que tenga conexion y si no sea 400
     * con su respectivo mensaje
     * @return void
     * @autor Cristobal Bravo
     */
    /** @test */
    public function testDispositivo(){
        if(DB::connection()){
            $response = $this->get(route('user.dispositivo'));
            $response->assertStatus(200);
            $response->assertok();
        }else{
            $response = $this->get(route('user.dispositivo'));
            $response->assertStatus(200);
            $response->assertJson(['mensaje'=>'No se ha podido realizar la peticion',
                                   'code'=>400]);
            $response->assertok();
        }
    }
    /**
     * Prueba basa en el metodo topUser el cual retorna
     * el top 10 usuarios que mas utiliza la plataforma
     * de forma descendete y ordenada
     * comprueba que el codigo del respuesta sea 200
     * en caso de que tenga conexion y si no sera 400
     * con su respectivo mensaje
     * @return void
     * @autor Cristobal Bravo
     */
    /** @test */
    public function testTopUser(){
        if(DB::connection()){
            $response = $this->get(route('user.top'));
            $response->assertStatus(200);
            $response->assertok();
        }else{
            $response = $this->get(route('user.top'));
            $response->assertStatus(200);
            $response->assertJson(['mensaje'=>'No se ha podido realizar la peticion',
                                   'code'=>400]);
            $response->assertok();
        }
    }
}
