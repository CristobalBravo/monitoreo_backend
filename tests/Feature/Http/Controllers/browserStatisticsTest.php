<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class browserStatisticsTest extends TestCase
{
    /**
     * Prueba basada en las estadististicas de los
     * de los navegadores que retorna el top 10 de
     * los navegadores mas utilizados.
     * en caso de que tenga conexion 200 y si no sea 400
     * con su respectivo mensaje
     * @return void
     * @autor Cristobal Bravo
     */
    /** @test */
    public function testTopBrowser(){
        if(DB::connection()){
            $response = $this->get(route('browser.top'));
            $response->assertStatus(200);
            $response->assertok();
        }else{
            $response = $this->get(route('browser.top'));
            $response->assertStatus(200);
            $response->assertJson(['mensaje'=>'No se ha podido realizar la peticion',
                                   'code'=>400]);
            $response->assertok();
        }
    }

    /**
     * Prueba basada en las estadististicas de los
     * de los SO Y que retorna cantidad de uso segun
     * las acciones de los usuarios en la plataforma.
     * en caso de que tenga conexion 200 y si no sea 400
     * con su respectivo mensaje
     * @return void
     * @autor Cristobal Bravo
     */
    /** @test */
    public function testBrowserSO(){
        if(DB::connection()){
            $response = $this->get(route('browser.so'));
            $response->assertStatus(200);
            $response->assertok();
        }else{
            $response = $this->get(route('browser.so'));
            $response->assertStatus(200);
            $response->assertJson(['mensaje'=>'No se ha podido realizar la peticion',
                                   'code'=>400]);
            $response->assertok();
        }
    }

    /**
     * Prueba basada en las estadististicas de los
     * de las versiones de los navegadores y que retorna
     * el version mas usada por cada browser
     * en caso de que tenga conexion 200 y si no sea 400
     * con su respectivo mensaje
     * @return void
     * @autor Cristobal Bravo
     */
    /** @test */
    public function testBrowserVersion(){
        if(DB::connection()){
            $response = $this->get(route('browser.topVersion'));
            $response->assertStatus(200);
            $response->assertok();
        }else{
            $response = $this->get(route('browser.topVersion'));
            $response->assertStatus(200);
            $response->assertJson(['mensaje'=>'No se ha podido realizar la peticion',
                                   'code'=>400]);
            $response->assertok();
        }
    }

    /**
     * Prueba basada en el listado de los browser que
     * tiene actualmente la plataforma
     * en caso de que tenga conexion 200 y si no sea 400
     * con su respectivo mensaje
     * @return void
     * @autor Cristobal Bravo
     */
    /** @test */
    public function testBrowserList(){
        if(DB::connection()){
            $response = $this->get(route('browser.list'));
            $response->assertStatus(200);
            $response->assertok();
        }else{
            $response = $this->get(route('browser.list'));
            $response->assertStatus(200);
            $response->assertJson(['mensaje'=>'No se ha podido realizar la peticion',
                                   'code'=>400]);
            $response->assertok();
        }
    }

    /**
     * Prueba basada en las estadististicas de los
     * de las versiones de los navegadores y que retorna
     * el version mas usada segun un navegador
     * en caso de que el request venga con el nombre vacio
     * entregara un status y codigo 400
     * con su respectivo mensaje
     * @return void
     * @autor Cristobal Bravo
     */
    /** @test */
    public function testTopVersionByBrowserWithoutRequest(){
            $data=['browser_name'=>''];
            $response = $this->post(route('browser.topVersionByBrowser',$data));
            $response->assertJson(['mensaje'=>'el servicio no pudo traer los datos',
                                   'status'=>'error',
                                   'code'=>400]);
            $response->assertStatus(200);
            $response->assertok();
    }

    /**
     * Prueba basada en las estadististicas de los
     * de las versiones de los navegadores y que retorna
     * el version mas usada segun un navegador
     * en caso de que el request venga vacio entregara un
     * status y codigo 400 con su respectivo mensaje
     * @return void
     * @autor Cristobal Bravo
     */
    /** @test */
    public function testTopVersionByBrowserWithEmptyRequest(){
        $data=[];
        $response = $this->post(route('browser.topVersionByBrowser',$data));
        $response->assertJson(['mensaje'=>'No ha ingresado el nombre del navegador',
                                'status'=>'error',
                               'code'=>400]);
        $response->assertStatus(200);
        $response->assertok();
}

     /**
     * Prueba basada en las estadististicas de los
     * de las versiones de los navegadores y que retorna
     * el version mas usada segun un navegador
     * en caso de que tenga conexion 200 y si no sea 400
     * con su respectivo mensaje
     * @return void
     * @autor Cristobal Bravo
     */
    /** @test */
    public function testTopVersionByBrowser(){
        if(DB::connection()){
            $data=['browser_name'=>'Chrome'];
            $response = $this->post(route('browser.topVersionByBrowser', $data));
            $response->assertStatus(200);
            $response->assertok();
        }else{
            $response = $this->post(route('browser.topVersionByBrowser'));
            $response->assertStatus(200);
            $response->assertJson(['code'=>400,
                                    'status'=>'error',
                                    'mensaje'=>'el servicio no pudo realizo la transaccion asc']);
            $response->assertok();
        }
    }
}



