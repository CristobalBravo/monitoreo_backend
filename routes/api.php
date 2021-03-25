<?php

use App\Models\log_mongo;
use App\Models\Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
//prueba de base de datos
Route::get('/test', function(){
    $cantidad= log_mongo::count();
    var_dump($cantidad);
});

Route::post('message/ingresar', 'UserStatisticController@ingresar');
Route::post('message/byDate', 'UserStatisticController@userByDate');

//graficos
Route::get('user/sevent', 'UserStatisticController@bySeventDay');
Route::get('user/top', 'UserStatisticController@topUsers')->name('user.top');
Route::get('user/dispositive', 'UserStatisticController@dispositivo')->name('user.dispositivo');
Route::get('user/byMonth', 'UserStatisticController@byMonth')->name('user.byMonth');
Route::get('user/byYear', 'UserStatisticController@byYear')->name('user.byYear');
Route::get('user/top/month', 'UserStatisticController@topUsersByMonth')->name('user.top.month');
Route::get('user/top/year', 'UserStatisticController@topUsersByYear')->name('user.top.year');
//browser
Route::get('browser/top', 'BrowserStatisticController@top')->name('browser.top');
Route::get('browser/top/month', 'BrowserStatisticController@topByMonth')->name('browser.top.month');
Route::get('browser/top/year', 'BrowserStatisticController@topByYear')->name('browser.top.year');
Route::get('browser/so', 'BrowserStatisticController@so')->name('browser.so');
Route::get('browser/version', 'BrowserStatisticController@browserVersion')->name('browser.topVersion');
Route::get('browser/list', 'BrowserStatisticController@browserList')->name('browser.list');
Route::post('browser/version/list', 'BrowserStatisticController@topVersionByBrowser')->name('browser.topVersionByBrowser');

//Pages
Route::get('pages/top', 'PagesStatisticsController@top')->name('page.top');
Route::get('pages/top/month', 'PagesStatisticsController@topByMonth')->name('page.top.month');
Route::get('pages/top/year', 'PagesStatisticsController@topByYear')->name('page.top.year');
//Modulo
Route::get('modulo/tipo/top', 'ModuloStatisticController@moduloTypeTop')->name('modulo.tipo.top');

//Memoria
//uri
Route::post('memory/uri/top', 'MemoryStatisticController@topMemoryByUri')->name('memory.uri.top');

//controller
Route::post('memory/controller/top', 'MemoryStatisticController@topMemoryByController')->name('memory.controller.top');
Route::post('memory/method/top', 'MemoryStatisticController@topMemoryByMethod')->name('memory.method.top');

//Tiempo de ejecucion
Route::post('executionTime/uri/top', 'ExecutionTimeStatisticsController@topExecutionTimeByUri')->name('executionTime.uri.top');
Route::post('executionTime/method/top', 'ExecutionTimeStatisticsController@topExecutionTimeByMethod')->name('executionTime.method.top');
Route::post('executionTime/controller/top', 'ExecutionTimeStatisticsController@topExecutionTimeByController')->name('executionTime.controller.top');

//usuario
Route::post('usuario/crear', 'UsuarioController@registro')->name('usuario.crear');
Route::post('usuario/editar', 'UsuarioController@editar')->name('usuario.editar');
Route::post('usuario/eliminar', 'UsuarioController@eliminar')->name('usuario.eliminar');
Route::get('usuario/listar', 'UsuarioController@listar')->name('usuario.listar');
Route::post('usuario/buscar', 'UsuarioController@buscar')->name('usuario.buscar');
Route::post('usuario/login', 'UsuarioController@login')->name('usuario.login');
Route::post('usuario/setPassword', 'UsuarioController@setPassword')->name('usuario.setPassword');


//configuracion sugerencias
Route::post('configuracionSugerencias/search', 'ConfiguracionSugerenciaController@search')->name('ConfiguracionSugerenciaController.search');
Route::post('configuracionSugerencias/editar', 'ConfiguracionSugerenciaController@update')->name('ConfiguracionSugerenciaController.editar');

//sugerencias por uri
Route::post('sugerencias/uri/execution_time', 'SugerenciasController@executionTimeByUri')->name('SugerenciasController.executionTimeByUri');
Route::post('sugerencias/uri/memory_peak', 'SugerenciasController@memoryPeakByUri')->name('SugerenciasController.memoryPeakByUri');
Route::post('sugerencias/uri/memory_usage', 'SugerenciasController@memoryUsageByUri')->name('SugerenciasController.memoryUsageByUri');


//sugerencias por controller
Route::post('sugerencias/controller/execution_time', 'SugerenciasController@executionTimeByController')->name('SugerenciasController.executionTimeByController');
Route::post('sugerencias/controller/memory_peak', 'SugerenciasController@memoryPeakByController')->name('SugerenciasController.memoryPeakByController');
Route::post('sugerencias/controller/memory_usage', 'SugerenciasController@memoryUsageByController')->name('SugerenciasController.memoryUsageByController');

//sugerencias por method
Route::post('sugerencias/method/execution_time', 'SugerenciasController@executionTimeByMethod')->name('Sugerenciasmethod.executionTimeByMethod');
Route::post('sugerencias/method/memory_peak', 'SugerenciasController@memoryPeakByMethod')->name('Sugerenciasmethod.memoryPeakByMethod');
Route::post('sugerencias/method/memory_usage', 'SugerenciasController@memoryUsageByMethod')->name('SugerenciasController.memoryUsageByMethod');

//sugerencias
Route::post('sugerencias/execution_time', 'SugerenciasController@obtenerSugereciasTiempoDeEjecucion')->name('Sugerenciasmethod.executionTimeByMethod');
Route::post('sugerencias/memory_peak', 'SugerenciasController@obtenerSugereciasMemoriaMaxima')->name('Sugerenciasmethod.memoryPeakByMethod');
Route::post('sugerencias/memory_usage', 'SugerenciasController@obtenerSugereciasMemoriaUsada')->name('SugerenciasController.memoryUsageByMethod');

//alertas
Route::get('alertas/all', 'AlertasController@getAllAlertsByDB')->name('alertas.all');
Route::get('alertas/all/PHP', 'AlertasController@getAllAlertsByPHP')->name('alertas.all');
Route::get('alertas/all/platform', 'AlertasController@getListPlatforms')->name('alertas.all.platform');
Route::get('alertas/cantidad/platform', 'AlertasController@obtenerCantidadPorPlataformas')->name('cantidad.platform');
Route::get('alertas/cantidad/code', 'AlertasController@obtenerCantidadPorCode')->name('cantidad.code');
Route::post('alertas/buscar/mensaje', 'AlertasController@buscarAlertaPorMensajeError')->name('alertas.buscar.mensaje');

//Notificaciones

Route::get('notificaciones/all', 'AlertasNotificacionesController@notificar')->name('notificaciones.all');
Route::post('notificaciones/ignorar', 'AlertasNotificacionesController@ignorar')->name('notificaciones.ignorar');
Route::post('notificaciones/visto', 'AlertasNotificacionesController@actualizarVisto')->name('notificaciones.visto');
Route::post('notificaciones/detalle', 'AlertasNotificacionesController@obtenerDetalleNotificacion')->name('notificaciones.detalle');
//pruebas de datos mongoDB

Route::get('test/1', 'SugerenciasController@pruebaSugerencia');


