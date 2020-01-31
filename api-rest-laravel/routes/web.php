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

/*
 * GET: conseguir datos
 * POST: Guardar datos o recuersos o hacer logica
 * PUT: actualizar datos o recursos
 * DELETE: Eliminar datos o recursos 
 */

Route::get('/entrada/pruebas', 'PostController@Pruebas');
Route::get('/categoria/pruebas', 'CategoryController@Pruebas');
Route::get('/usuario/pruebas', 'UserController@Pruebas');

//RUTAS DEL CONTROLADOR USUARIO

Route::post('/api/userRegister', 'UserController@Register');
Route::post('/api/userLogin', 'UserController@Login');
Route::put('/api/user/update', 'UserController@Update');
Route::get('api/user/avatar/{filename?}', 'UserController@getImage');
Route::get('api/user/detail/{id?}', 'UserController@detail');
Route::group(['middleware' => ['ApiAuthMiddleware']], function() {
    Route::post('/api/user/uploadImage', 'UserController@UploadImage'); 
});

// RUTAS DEL CONTROLADOR DE CATEGORIAS

Route::resource('/api/category', 'CategoryController');

// RUTAS DEL CONTROLADOR DE POSTS

Route::resource('api/post', 'PostController');
Route::post('/api/post/upload', 'PostController@Upload');
Route::get('/api/post/image/{filename}', 'PostController@getImage');
Route::get('/api/post/user/{id}', 'PostController@getPostsByUser');
Route::get('/api/post/category/{id}', 'PostController@getPostsByCategory');

//RUTAS DE PRUEBA

Route::get('/', function() {
    return view('welcome');
});

Route::get('/prueba/{nombre?}/{apellido?}', function($nombre = null, $apellido = null) {
    return view('prueba', array(
        'nombre' => $nombre,
        'apellido' => $apellido
    ));
});

Route::get('/animales', 'PruebasController@index');
Route::get('/testORM', 'PruebasController@testORM');

