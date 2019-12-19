<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/','HomeController@index');

Auth::routes();
///////////////// RUTAS WEB
    Route::get('/comandos/aviso_actividad', 'ActividadController@aviso_actividad')->name('aviso_actividad');
///////////////// RUTAS PRINCIPALES
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/welcome', 'HomeController@index')->name('welcome');

    /*Propiedad*/
    Route::get('/propiedades', 'PropiedadController@index')->name('propiedades');
    Route::post('/propiedades/store', 'PropiedadController@store')->name('propiedades-store');
    Route::get('/propiedades/show/{id}/{procedencia}/{padre}', 'PropiedadController@show')->name('propiedades-show');
    Route::post('/propiedades/update/{id}', 'PropiedadController@update')->name('propiedades-update');
    Route::post('/propiedades/update/{id}', 'PropiedadController@update')->name('propiedades-update');
    Route::get('/propiedades/destroy/{id}', 'PropiedadController@destroy')->name('propiedades-destroy');
    Route::get('/propiedades/updateinfo', 'PropiedadController@updateinfo')->name('propiedades-updateinfo');

    /*Poliza WS*/
    Route::get('/poliza_ws', 'PolizaWSController@ws_poliza')->name('poliza_ws');
    Route::post('/poliza_ws/enviar', 'PolizaWSController@enviar')->name('poliza_ws-enviar');

    /*Documentos*/
    Route::get('/documentos/documento', 'FileController@index')->name('documentos');
    Route::post('/documentos/documento/store/{procedencia}', 'FileController@store')->name('documento-store');
    Route::get('/documentos/documento/show/{id}/{procedencia}', 'FileController@show')->name('documento-show');
    Route::post('/documentos/documento/update/{id}/{procedencia}', 'FileController@update')->name('documento-update');
    Route::get('/documentos/documento/destroy/{id}/{procedencia}', 'FileController@destroy')->name('documento-destroy');
    Route::get('storage/{archivo}', function ($archivo) {
        $public_path = public_path();
        $url = $public_path.'/uploads/'.$archivo;
        //verificamos si el archivo existe y lo retornamos

        return response()->download($url);
    });

    /*AJAX */
    Route::get('/catalogo-propiedades/{id}', 'CatalogosAjaxController@CatalogosPropiedades');
    Route::get('/catalogo-estados/{id}', 'CatalogosAjaxController@CatalogosEstados');

    Route::get('/catalogo-ciudades/{id}', 'CatalogosAjaxController@CatalogosCiudades');
    Route::get('/catalogo-bancos/{id}', 'CatalogosAjaxController@CatalogosBancos');
    Route::get('/catalogo-niveles/{id}', 'CatalogosAjaxController@CatalogosNiveles');
    Route::get('/catalogo-proyectos/{id}', 'CatalogosAjaxController@CatalogosProyectos');
    Route::get('/catalogo-prospecto/{id}', 'CatalogosAjaxController@CatalogosCliente');
    Route::get('/catalogo-colores/{id}', 'CatalogosAjaxController@CatalogosColores');
    Route::get('/catalogo-propiedades-desarrollo/{id}', 'CatalogosAjaxController@CatalogosPropiedadesDesarrollo');
    Route::get('/catalogo-propiedades-desarrollo-estatus/{id}', 'CatalogosAjaxController@CatalogosPropiedadesDesarrolloSinEstatus');
    /*Actividades y mensajes*/
    Route::get('/actividades-hoy', 'CatalogosAjaxController@actividades_hoy');
    Route::get('/mensajes-nuevos', 'CatalogosAjaxController@mensajes_nuevos');
    Route::get('/catalogo-cotizaciones-contrato/{prospecto}', 'CatalogosAjaxController@CatalogosCotizacionesContrato');

/////////////// CATALOGOS
    /*Agentes*/
    Route::get('/catalogos/usuarios', 'AgenteController@index')->name('usuarios');
    Route::post('/catalogos/usuarios/store', 'AgenteController@store')->name('usuarios-store');
    Route::get('/catalogos/usuarios/show/{id}', 'AgenteController@show')->name('usuarios-show');
    Route::get('/catalogos/usuarios/profile/{id}', 'AgenteController@profile')->name('usuarios-profile');
    Route::post('/catalogos/usuarios/update/{id}', 'AgenteController@update')->name('usuarios-update');
    Route::post('/catalogos/usuarios/updateprofile/{id}', 'AgenteController@updateprofile')->name('usuarios-updateprofile');
    Route::get('/catalogos/usuarios/destroy/{id}', 'AgenteController@destroy')->name('usuarios-destroy');
    Route::get('/catalogos/usuarios/activa/{id}', 'AgenteController@activa')->name('usuarios-activa');
    Route::get('/catalogos/usuarios/inactiva/{id}', 'AgenteController@inactiva')->name('usuarios-inactiva');

    /*usuarios externos*/
    Route::get('/catalogos/usuarios_externos', 'UsuariosExternosController@index')->name('usuarios_externos');
    Route::post('/catalogos/usuarios_externos/store', 'UsuariosExternosController@store')->name('usuarios_externos-store');
    Route::get('/catalogos/usuarios_externos/show/{id}', 'UsuariosExternosController@show')->name('usuarios_externos-show');
    Route::post('/catalogos/usuarios_externos/update/{id}', 'UsuariosExternosController@update')->name('usuarios_externos-update');
    Route::get('/catalogos/usuarios_externos/destroy/{id}', 'UsuariosExternosController@destroy')->name('usuarios_externos-destroy');

    /*roles*/
    Route::get('/catalogos/rol', 'RolController@index')->name('rol');
    Route::post('/catalogos/rol/store', 'RolController@store')->name('rol-store');
    Route::get('/catalogos/rol/show/{id}', 'RolController@show')->name('rol-show');
    Route::post('/catalogos/rol/update/{id}', 'RolController@update')->name('rol-update');
    Route::get('/catalogos/rol/destroy/{id}', 'RolController@destroy')->name('rol-destroy');
/////////////// CARGA E INFORMACION
    Route::get('/carga/imagen/', 'CargaInfoController@subirImagenes')->name('subir-imagenes');
