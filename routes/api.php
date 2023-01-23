<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes de MOODLE
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
//*******************  Rutas de Usuarios **********************************
Route::get('consultaUsers/{matricula}','UsersApiController@getAlumnos'); {   
}

Route::post('creaUsuarios','UsersApiController@CrearAlumnos');{   
}

Route::put('updateUsers/{matricula}','UsersApiController@UpdateUsers'); {   
}
//************** */ Rutas De Plan de Estudios ***************************
Route::post('creaCarrera','PlanEstudiosApiController@CreaCarrera');{   
}

Route::post('creaCuatrimestres','PlanEstudiosApiController@CreaCuatrimestres');{   
}

Route::post('creaMaterias','PlanEstudiosApiController@CreaMaterias');{   
}

Route::get('consultaMateria/{ConsultaMaterias}','PlanEstudiosApiController@ConsultaMateria');{   
}

Route::get('calificacionMateria/{calificacionMateria}','PlanEstudiosApiController@CalificacionMateria');{   
}

//* Rutas De Proceso de Cambios del Users una ves Creado (enrolar,desenrolar,Suspender, Activar)

Route::post('enrolarUsersCurse','CambiosEstadoControllers@EnrolarUsersCurse');{   
}

Route::post('desenrolarUsersCurse','CambiosEstadoControllers@DesenrolarUsersCurse');{   
}

Route::post('suspenderUsers','CambiosEstadoControllers@SuspenderUsers');{   
}

Route::post('activarUsers','CambiosEstadoControllers@AbilitarUser');{   
}
